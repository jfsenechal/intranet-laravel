<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Service\TripAttributeResolver;

beforeEach(function (): void {
    $this->resolver = new TripAttributeResolver;

    // The June 2026 period, closing on the last day of the month.
    $this->june = Rate::factory()->create([
        'amount' => 0.5055,
        'omnium' => 0.0062,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
    ]);

    Rate::factory()->create([
        'amount' => 0.4440,
        'omnium' => 0.0062,
        'start_date' => '2026-07-01',
        'end_date' => '2026-09-30',
    ]);
});

test('resolves the June rate for an external trip departing on the last day of the period', function (): void {
    $trip = Trip::factory()->make([
        'departure_date' => '2026-06-30 00:00:00',
        'arrival_date' => '2026-06-30 00:00:00',
        'departure_location' => 'Marche-en-Famenne',
        'arrival_location' => 'Namur',
    ]);

    expect($this->resolver->resolveRate($trip)?->id)->toBe($this->june->id);
});

test('resolves the June rate for a trip leaving during the last day of the period', function (): void {
    $trip = Trip::factory()->make([
        'departure_date' => '2026-06-30 08:30:00',
        'arrival_date' => '2026-06-30 17:00:00',
        'departure_location' => 'Marche-en-Famenne',
        'arrival_location' => 'Namur',
    ]);

    expect($this->resolver->resolveRate($trip)?->id)->toBe($this->june->id);
});
