<?php

declare(strict_types=1);

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Dto\DeclarationSummary;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;

beforeEach(function () {
    $this->declaration = Declaration::factory()->create([
        'rate' => 0.40,
        'rate_omnium' => 0.02,
        'omnium' => false,
    ]);
});

describe('calculate', function () {
    test('returns a DeclarationSummary with all calculated values', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'distance' => 100,
            'meal_expense' => 15.00,
            'train_expense' => 25.00,
        ]);

        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'distance' => 50,
            'meal_expense' => 10.00,
            'train_expense' => 0.00,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);
        $summary = $calculator->calculate();

        expect($summary)->toBeInstanceOf(DeclarationSummary::class)
            ->and($summary->totalKilometers)->toBe(150)
            ->and($summary->totalMileageAllowance)->toBe(60.00)
            ->and($summary->totalOmnium)->toBe(0.00)
            ->and($summary->totalRefund)->toBe(60.00)
            ->and($summary->mealExpense)->toBe(25.00)
            ->and($summary->trainExpense)->toBe(25.00)
            ->and($summary->totalExpense)->toBe(50.00);
    });
});

describe('calculateTotalKilometers', function () {
    test('sums all trip distances', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'distance' => 100,
        ]);

        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'distance' => 75,
        ]);

        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'distance' => 50,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalKilometers())->toBe(225);
    });

    test('returns zero when no trips exist', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalKilometers())->toBe(0);
    });
});

describe('calculateTotalMileageAllowance', function () {
    test('multiplies total kilometers by rate', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalMileageAllowance(100))->toBe(40.00)
            ->and($calculator->calculateTotalMileageAllowance(250))->toBe(100.00);
    });

    test('rounds to 2 decimal places', function () {
        $declaration = Declaration::factory()->create([
            'rate' => 0.42,
        ]);
        $declaration->load('trips');

        $calculator = new DeclarationCalculator($declaration);

        // 100 * 0.42 = 42.00
        expect($calculator->calculateTotalMileageAllowance(100))->toBe(42.00)
            ->and($calculator->calculateTotalMileageAllowance(33))->toBe(13.86); // 33 * 0.42 = 13.86
    });
});

describe('calculateTotalOmnium', function () {
    test('returns zero when omnium is false', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalOmnium(100))->toBe(0.00);
    });

    test('calculates omnium when omnium is true', function () {
        $declaration = Declaration::factory()->create([
            'rate_omnium' => 0.02,
            'omnium' => true,
        ]);
        $declaration->load('trips');

        $calculator = new DeclarationCalculator($declaration);

        expect($calculator->calculateTotalOmnium(100))->toBe(2.00)
            ->and($calculator->calculateTotalOmnium(250))->toBe(5.00);
    });
});

describe('calculateTotalRefund', function () {
    test('subtracts omnium from mileage allowance', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalRefund(100.00, 10.00))->toBe(90.00)
            ->and($calculator->calculateTotalRefund(50.00, 5.00))->toBe(45.00);
    });

    test('handles zero omnium', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalRefund(100.00, 0.00))->toBe(100.00);
    });
});

describe('calculateMealExpense', function () {
    test('sums all trip meal expenses', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'meal_expense' => 15.50,
        ]);

        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'meal_expense' => 20.25,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateMealExpense())->toBe(35.75);
    });

    test('returns zero when no meal expenses', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'meal_expense' => null,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateMealExpense())->toBe(0.00);
    });
});

describe('calculateTrainExpense', function () {
    test('sums all trip train expenses', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'train_expense' => 30.00,
        ]);

        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'train_expense' => 45.50,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTrainExpense())->toBe(75.50);
    });

    test('returns zero when no train expenses', function () {
        Trip::factory()->create([
            'declaration_id' => $this->declaration->id,
            'train_expense' => null,
        ]);

        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTrainExpense())->toBe(0.00);
    });
});

describe('calculateTotalExpense', function () {
    test('sums meal and train expenses', function () {
        $this->declaration->load('trips');

        $calculator = new DeclarationCalculator($this->declaration);

        expect($calculator->calculateTotalExpense(25.00, 30.00))->toBe(55.00)
            ->and($calculator->calculateTotalExpense(0.00, 50.00))->toBe(50.00)
            ->and($calculator->calculateTotalExpense(50.00, 0.00))->toBe(50.00);
    });
});

describe('DeclarationSummary toArray', function () {
    test('returns array representation of summary', function () {
        $summary = new DeclarationSummary(
            totalKilometers: 150,
            totalMileageAllowance: 60.00,
            totalOmnium: 3.00,
            totalRefund: 57.00,
            mealExpense: 25.00,
            trainExpense: 25.00,
            totalExpense: 50.00,
        );

        expect($summary->toArray())->toBe([
            'total_kilometers' => 150,
            'total_mileage_allowance' => 60.00,
            'total_omnium' => 3.00,
            'total_refund' => 57.00,
            'meal_expense' => 25.00,
            'train_expense' => 25.00,
            'total_expense' => 50.00,
        ]);
    });
});
