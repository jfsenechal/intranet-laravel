<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Calculator;

use AcMarche\Mileage\Dto\DeclarationSummary;
use AcMarche\Mileage\Models\Declaration;

final readonly class DeclarationCalculator
{
    public function __construct(
        private Declaration $declaration,
        private TripAmountCalculator $amountCalculator = new TripAmountCalculator(),
    ) {}

    public function calculate(): DeclarationSummary
    {
        $totalKilometers = $this->calculateTotalKilometers();
        $totalMileageAllowance = $this->calculateTotalMileageAllowance($totalKilometers);
        $totalOmnium = $this->calculateTotalOmnium($totalKilometers);
        $mealExpense = $this->calculateMealExpense();
        $trainExpense = $this->calculateTrainExpense();
        $totalExpense = $this->calculateTotalExpense($mealExpense, $trainExpense);
        $totalRefund = $this->calculateTotalRefund($totalMileageAllowance, $totalOmnium, $totalExpense);

        return new DeclarationSummary(
            totalKilometers: $totalKilometers,
            totalMileageAllowance: $totalMileageAllowance,
            totalOmnium: $totalOmnium,
            totalRefund: $totalRefund,
            mealExpense: $mealExpense,
            trainExpense: $trainExpense,
            totalExpense: $totalExpense,
        );
    }

    public function calculateTotalKilometers(): int
    {
        return (int) $this->declaration->trips->sum('distance');
    }

    public function calculateTotalMileageAllowance(int $totalKilometers): float
    {
        return $this->amountCalculator->amount($totalKilometers, (float) $this->declaration->rate);
    }

    public function calculateTotalOmnium(int $totalKilometers): float
    {
        if (! $this->declaration->omnium) {
            return 0.0;
        }

        return $this->amountCalculator->amount($totalKilometers, (float) $this->declaration->rate_omnium);
    }

    /**
     * The reimbursement certified to the beneficiary: the mileage allowance net
     * of the omnium retention, plus the meal and train expenses. Mirrors the
     * legacy `Declaration::totalARembourser` figure.
     */
    public function calculateTotalRefund(float $totalMileageAllowance, float $totalOmnium, float $totalExpense): float
    {
        return round($totalMileageAllowance - $totalOmnium + $totalExpense, 2);
    }

    public function calculateMealExpense(): float
    {
        return round((float) $this->declaration->trips->sum('meal_expense'), 2);
    }

    public function calculateTrainExpense(): float
    {
        return round((float) $this->declaration->trips->sum('train_expense'), 2);
    }

    public function calculateTotalExpense(float $mealExpense, float $trainExpense): float
    {
        return round($mealExpense + $trainExpense, 2);
    }
}
