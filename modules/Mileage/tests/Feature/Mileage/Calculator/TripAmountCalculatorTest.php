<?php

declare(strict_types=1);

use AcMarche\Mileage\Calculator\TripAmountCalculator;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->calculator = new TripAmountCalculator();
    $this->user = User::factory()->create();
});

describe('forTrip', function (): void {
    test('returns distance times rate minus omnium', function (): void {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'distance' => 100,
            'rate' => 0.40,
            'omnium' => 0.03,
        ]);

        expect($this->calculator->forTrip($trip))->toBe(37.0);
    });

    test('treats null rate and omnium as zero', function (): void {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'distance' => 50,
            'rate' => null,
            'omnium' => null,
        ]);

        expect($this->calculator->forTrip($trip))->toBe(0.0);
    });
});

describe('forQuery', function (): void {
    test('sums distance times rate minus omnium across trips', function (): void {
        Trip::factory()->create([
            'user_id' => $this->user->id,
            'distance' => 10,
            'rate' => 0.40,
            'omnium' => 0.03,
        ]);
        Trip::factory()->create([
            'user_id' => $this->user->id,
            'distance' => 25,
            'rate' => 0.40,
            'omnium' => 0.03,
        ]);

        // (10 * 0.37) + (25 * 0.37) = 3.70 + 9.25 = 12.95
        expect($this->calculator->forQuery(Trip::query()->toBase()))->toBe(12.95);
    });
});
