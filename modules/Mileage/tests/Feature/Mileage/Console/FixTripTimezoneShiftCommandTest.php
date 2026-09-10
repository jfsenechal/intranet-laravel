<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Trip;

/**
 * Store a trip exactly as the converting picker left it, without going through
 * the observer that would resolve a rate from the shifted departure date.
 *
 * @param  array<string, mixed>  $attributes
 */
function tripStoredAt(string $departureDate, ?string $arrivalDate, string $createdAt, array $attributes = []): Trip
{
    $trip = Trip::factory()->create($attributes);

    $trip->forceFill([
        'departure_date' => $departureDate,
        'arrival_date' => $arrivalDate,
        'created_at' => $createdAt,
    ])->saveQuietly();

    return $trip->refresh();
}

it('puts an external trip back on the Belgian clock', function (): void {
    $trip = tripStoredAt('2026-09-07 22:00:00', '2026-09-08 06:50:00', '2026-09-10 08:44:42');

    $this->artisan('mileage:fix-trip-timezone-shift')
        ->expectsOutputToContain('Moved 1 trip(s)')
        ->assertSuccessful();

    $trip->refresh();

    expect($trip->departure_date->format('Y-m-d H:i:s'))->toBe('2026-09-08 00:00:00')
        ->and($trip->arrival_date->format('Y-m-d H:i:s'))->toBe('2026-09-08 08:50:00');
});

it('applies the winter offset outside daylight saving time', function (): void {
    $trip = tripStoredAt('2026-01-14 23:00:00', '2026-01-15 07:30:00', '2026-08-20 09:00:00');

    $this->artisan('mileage:fix-trip-timezone-shift')->assertSuccessful();

    $trip->refresh();

    expect($trip->departure_date->format('Y-m-d H:i:s'))->toBe('2026-01-15 00:00:00')
        ->and($trip->arrival_date->format('Y-m-d H:i:s'))->toBe('2026-01-15 08:30:00');
});

it('leaves internal trips alone', function (): void {
    // Their time inputs are hidden, so Filament never applied its timezone to them.
    $trip = tripStoredAt('2026-09-08 00:00:00', null, '2026-09-10 08:44:42');

    $this->artisan('mileage:fix-trip-timezone-shift')
        ->expectsOutputToContain('No trip to put back on the Belgian clock.')
        ->assertSuccessful();

    expect($trip->refresh()->departure_date->format('Y-m-d H:i:s'))->toBe('2026-09-08 00:00:00');
});

it('leaves trips created before the window alone', function (): void {
    $trip = tripStoredAt('2026-07-01 09:00:00', '2026-07-01 17:00:00', '2026-07-02 10:00:00');

    $this->artisan('mileage:fix-trip-timezone-shift')
        ->expectsOutputToContain('No trip to put back on the Belgian clock.')
        ->assertSuccessful();

    expect($trip->refresh()->departure_date->format('Y-m-d H:i:s'))->toBe('2026-07-01 09:00:00');
});

it('writes nothing on a dry run', function (): void {
    $trip = tripStoredAt('2026-09-07 22:00:00', '2026-09-08 06:50:00', '2026-09-10 08:44:42');

    $this->artisan('mileage:fix-trip-timezone-shift', ['--dry-run' => true])
        ->expectsOutputToContain('1 trip(s) would be moved back')
        ->assertSuccessful();

    expect($trip->refresh()->departure_date->format('Y-m-d H:i:s'))->toBe('2026-09-07 22:00:00');
});
