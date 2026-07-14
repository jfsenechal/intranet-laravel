<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->declaration = Declaration::factory()->create();
});

/**
 * Create a declared trip and force a stored rate/omnium without triggering the
 * rate-resolving observer (which only recomputes when departure_date changes).
 */
function declaredTripWithRate(int $userId, int $declarationId, string $departureDate, float $rate, float $omnium): Trip
{
    $trip = Trip::factory()->create([
        'user_id' => $userId,
        'declaration_id' => $declarationId,
        'departure_date' => $departureDate,
    ]);

    $trip->update(['rate' => $rate, 'omnium' => $omnium]);

    return $trip;
}

test('passes when declared trips carry the applicable rate', function (): void {
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.4000,
        'omnium' => 0.0300,
    ]);

    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('All declared trips carry the correct rate.')
        ->assertSuccessful();
});

test('fails when a declared trip has an incorrect rate', function (): void {
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.4000,
        'omnium' => 0.0300,
    ]);

    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->assertFailed();
});

test('ignores undeclared trips', function (): void {
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.4000,
        'omnium' => 0.0300,
    ]);

    // Undeclared trip with a wrong rate must not be reported.
    $trip = Trip::factory()->create([
        'user_id' => $this->user->id,
        'declaration_id' => null,
        'departure_date' => '2026-06-15',
    ]);
    $trip->update(['rate' => 0.9900, 'omnium' => 0.0300]);

    $this->artisan('mileage:verify-trip-rates')
        ->assertSuccessful();
});

test('--fix corrects the stored rate and omnium', function (): void {
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.4000,
        'omnium' => 0.0300,
    ]);

    $trip = declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.0100);

    $this->artisan('mileage:verify-trip-rates', ['--fix' => true])
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.0300);
});

test('warns when no applicable rate exists for a declared trip', function (): void {
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('no applicable rate found')
        ->assertSuccessful();
});
