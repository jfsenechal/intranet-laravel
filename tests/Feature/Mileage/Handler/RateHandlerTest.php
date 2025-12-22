<?php

declare(strict_types=1);

use AcMarche\Mileage\Handler\RateHandler;
use AcMarche\Mileage\Models\Rate;

describe('hasOverlappingRate', function () {
    test('detects overlap when new range starts before existing range ends', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-03-01 to 2024-09-30 (overlaps with existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-03-01', '2024-09-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range ends after existing range starts', function () {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-08-31 (overlaps with existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-08-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range is completely inside existing range', function () {
        // Existing rate: 2024-01-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-03-01 to 2024-06-30 (completely inside existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-03-01', '2024-06-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range completely contains existing range', function () {
        // Existing rate: 2024-03-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-03-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-01-01 to 2024-12-31 (completely contains existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-12-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when ranges share the same start date', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-01-01 to 2024-03-31 (same start date)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-03-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when ranges share the same end date', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-03-01 to 2024-06-30 (same end date)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-03-01', '2024-06-30', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range starts on existing end date', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-06-30 to 2024-12-31 (starts on existing end date - overlap)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-06-30', '2024-12-31', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('detects overlap when new range ends after existing start date', function () {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-06-02 (ends after existing start date - overlap)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-06-02', null);

        expect($hasOverlap)->toBeTrue();
    });

    test('no overlap when new range is completely before existing range', function () {
        // Existing rate: 2024-06-01 to 2024-12-31
        Rate::factory()->create([
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        // New rate: 2024-01-01 to 2024-05-31 (completely before existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-05-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('no overlap when new range is completely after existing range', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-07-01 to 2024-12-31 (completely after existing)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-07-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('no overlap when no rates exist', function () {
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });

    test('ignores current record when editing', function () {
        // Create a rate
        $existingRate = Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // When editing the same rate, should not detect overlap with itself
        $hasOverlap = RateHandler::hasOverlappingRate('2024-01-01', '2024-06-30', $existingRate);

        expect($hasOverlap)->toBeFalse();
    });

    test('detects overlap with other rates when editing', function () {
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
        $hasOverlap = RateHandler::hasOverlappingRate('2024-03-01', '2024-09-30', $rateBeingEdited);

        expect($hasOverlap)->toBeTrue();
    });

    test('allows adjacent ranges without gap', function () {
        // Existing rate: 2024-01-01 to 2024-06-30
        Rate::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        // New rate: 2024-07-01 to 2024-12-31 (adjacent, no gap, no overlap)
        $hasOverlap = RateHandler::hasOverlappingRate('2024-07-01', '2024-12-31', null);

        expect($hasOverlap)->toBeFalse();
    });
});
