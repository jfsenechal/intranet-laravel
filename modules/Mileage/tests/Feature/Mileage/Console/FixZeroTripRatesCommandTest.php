<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    // The declaration is the source of truth for a declared trip, and the
    // factory randomises its rate, hence pinning it here.
    $this->declaration = Declaration::factory()->create([
        'omnium' => true,
        'rate' => 0.4000,
        'rate_omnium' => 0.0300,
    ]);
});

/**
 * Create a declared trip and force a stored rate/omnium without triggering the
 * rate-resolving observer (which only recomputes when departure_date changes).
 */
function declaredTripStoredAt(int $userId, int $declarationId, string $departureDate, float $rate, float $omnium): Trip
{
    $trip = Trip::factory()->create([
        'user_id' => $userId,
        'declaration_id' => $declarationId,
        'departure_date' => $departureDate,
    ]);

    $trip->update(['rate' => $rate, 'omnium' => $omnium]);

    return $trip;
}

test('copies the declaration rate and omnium onto a zero-rate declared trip', function (): void {
    $trip = declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates')
        ->expectsOutputToContain('Aligned 1 trip(s)')
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.0300);
});

test('leaves declared trips that already carry a rate untouched', function (): void {
    // A stale non-zero rate is the business of mileage:verify-trip-rates.
    $trip = declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.0100);

    $this->artisan('mileage:fix-zero-trip-rates')
        ->expectsOutputToContain('No declared trip carries a zero rate.')
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.3000)
        ->and((float) $trip->omnium)->toBe(0.0100);
});

test('ignores undeclared trips stored with a zero rate', function (): void {
    $trip = Trip::factory()->create([
        'user_id' => $this->user->id,
        'declaration_id' => null,
        'departure_date' => '2026-06-15',
    ]);
    $trip->update(['rate' => 0.0000, 'omnium' => 0.0000]);

    $this->artisan('mileage:fix-zero-trip-rates')
        ->expectsOutputToContain('No declared trip carries a zero rate.')
        ->assertSuccessful();

    expect((float) $trip->refresh()->rate)->toBe(0.0000);
});

test('takes the declaration rate, not the rate applicable today', function (): void {
    // The rates table moved after the declaration was filed. What was paid is
    // the declaration's snapshot, so that is what the trip must carry.
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.9999,
        'omnium' => 0.0900,
    ]);

    $trip = declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates')->assertSuccessful();

    expect((float) $trip->refresh()->rate)->toBe(0.4000);
});

test('--dry-run reports the trips without writing them', function (): void {
    $trip = declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates', ['--dry-run' => true])
        ->expectsOutputToContain('would take their declaration rate')
        ->assertSuccessful();

    expect((float) $trip->refresh()->rate)->toBe(0.0000);
});

test('writes a zero omnium for a declaration not entitled to one', function (): void {
    $declaration = Declaration::factory()->create([
        'omnium' => false,
        'rate' => 0.4000,
        'rate_omnium' => 0.0300,
    ]);
    $trip = declaredTripStoredAt($this->user->id, $declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates')->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.0000);
});

test('skips a trip whose declaration carries a zero rate too', function (): void {
    $declaration = Declaration::factory()->create([
        'omnium' => true,
        'rate' => 0.0000,
        'rate_omnium' => 0.0300,
    ]);
    $trip = declaredTripStoredAt($this->user->id, $declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates')
        ->expectsOutputToContain('carries a zero rate too')
        ->assertSuccessful();

    expect((float) $trip->refresh()->rate)->toBe(0.0000);
});

test('skips a trip whose declaration no longer exists', function (): void {
    $trip = declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->declaration->delete();

    $this->artisan('mileage:fix-zero-trip-rates')
        ->expectsOutputToContain('no longer exists')
        ->assertSuccessful();

    expect((float) $trip->refresh()->rate)->toBe(0.0000);
});

test('leaves the fixed trip passing mileage:verify-trip-rates', function (): void {
    declaredTripStoredAt($this->user->id, $this->declaration->id, '2026-06-15', 0.0000, 0.0000);

    $this->artisan('mileage:fix-zero-trip-rates')->assertSuccessful();

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('All declared trips match their declaration.')
        ->assertSuccessful();
});
