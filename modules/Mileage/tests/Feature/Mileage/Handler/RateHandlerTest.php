<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Validator\RateOverlapValidator;

describe('hasOverlappingRate', function (): void {
    test('detects overlap when new range starts before existing range ends', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-03-01 to 2024-09-30 (overlaps with existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-03-01', '2024-09-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range ends after existing range starts', function (): void {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-08-31 (overlaps with existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-08-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range is completely inside existing range', function (): void {
        // Existing rate: 2024-01-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-03-01 to 2024-06-30 (completely inside existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-03-01', '2024-06-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range completely contains existing range', function (): void {
        // Existing rate: 2024-03-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-03-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-01-01 to 2024-12-31 (completely contains existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-12-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when ranges share the same start date', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-01-01 to 2024-03-31 (same start date)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-03-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when ranges share the same end date', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-03-01 to 2024-06-30 (same end date)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-03-01', '2024-06-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range starts on existing end date', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-06-30 to 2024-12-31 (starts on existing end date - overlap)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-06-30', '2024-12-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range ends after existing start date', function (): void {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-06-02 (ends after existing start date - overlap)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-06-02', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('no overlap when new range is completely before existing range', function (): void {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-05-31 (completely before existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-05-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('no overlap when new range is completely after existing range', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-07-01 to 2024-12-31 (completely after existing)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-07-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('no overlap when no rates exist', function (): void {
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('ignores current record when editing', function (): void {
        // Create a rate
        $existingRate = Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // When editing the same rate, should not detect overlap with itself
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-01-01', '2024-06-30', $existingRate);

        expect($hasOverlap)->toBeFalse();
    });

    test('detects overlap with other rates when editing', function (): void {
        // Create two rates
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        $rateBeingEdited = Rate::factory()->create([
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
        ]);

        // When editing second rate to overlap with first, should detect overlap
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-03-01', '2024-09-30', $rateBeingEdited);

        expect($hasOverlap)->toBeTrue();
    });

    test('allows adjacent ranges without gap', function (): void {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-07-01 to 2024-12-31 (adjacent, no gap, no overlap)
        $hasOverlap = RateOverlapValidator::hasOverlappingRate('2024-07-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });
});

describe('rate creation back-fills undeclared trips', function (): void {
    test('applies the new rate to undeclared trips within its period', function (): void {
        $trip = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2024-06-15 08:00:00',
            'rate' => 0.30,
            'omnium' => 0.01,
        ]);

        Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        expect($trip->refresh()->rate)->toBe('0.4400')
            ->and($trip->omnium)->toBe('0.0300');
    });

    test('leaves trips already linked to a declaration untouched', function (): void {
        $declaration = Declaration::factory()->create();

        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'departure_date' => '2024-06-15 08:00:00',
            'rate' => 0.30,
            'omnium' => 0.01,
        ]);

        Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        expect($trip->refresh()->rate)->toBe('0.3000')
            ->and($trip->omnium)->toBe('0.0100');
    });

    test('leaves undeclared trips outside the rate period untouched', function (): void {
        $before = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2023-12-31 08:00:00',
            'rate' => 0.30,
        ]);

        $after = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2025-01-01 08:00:00',
            'rate' => 0.30,
        ]);

        Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        expect($before->refresh()->rate)->toBe('0.3000')
            ->and($after->refresh()->rate)->toBe('0.3000');
    });
});

describe('rate updates re-apply to undeclared trips', function (): void {
    test('applies a corrected amount to undeclared trips in the period', function (): void {
        $trip = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2024-06-15 08:00:00',
            'rate' => 0.30,
            'omnium' => 0.01,
        ]);

        $rate = Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $rate->update([
            'amount' => 0.50,
            'omnium' => 0.05,
        ]);

        expect($trip->refresh()->rate)->toBe('0.5000')
            ->and($trip->omnium)->toBe('0.0500');
    });

    test('leaves trips already linked to a declaration untouched', function (): void {
        $declaration = Declaration::factory()->create();

        $trip = Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'departure_date' => '2024-06-15 08:00:00',
            'rate' => 0.30,
            'omnium' => 0.01,
        ]);

        $rate = Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $rate->update(['amount' => 0.50]);

        expect($trip->refresh()->rate)->toBe('0.3000')
            ->and($trip->omnium)->toBe('0.0100');
    });

    test('picks up trips brought into the period by an extended end date', function (): void {
        $trip = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2025-03-01 08:00:00',
            'rate' => 0.30,
        ]);

        $rate = Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        expect($trip->refresh()->rate)->toBe('0.3000');

        $rate->update(['end_date' => '2025-12-31']);

        expect($trip->refresh()->rate)->toBe('0.4400');
    });

    test('does not rewrite trips when no rate value changed', function (): void {
        $trip = Trip::factory()->create([
            'declaration_id' => null,
            'departure_date' => '2024-06-15 08:00:00',
            'rate' => 0.30,
        ]);

        $rate = Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        // Diverge the trip behind the observer's back, then save the rate
        // without touching any value a trip copies. Travelling forward keeps
        // the touch a real update, otherwise updated_at is unchanged and the
        // save short-circuits before the observer ever runs.
        Trip::query()->whereKey($trip->id)->update(['rate' => 0.30]);

        $this->travel(1)->minutes();
        $rate->touch();

        expect($trip->refresh()->rate)->toBe('0.3000');
    });
});
