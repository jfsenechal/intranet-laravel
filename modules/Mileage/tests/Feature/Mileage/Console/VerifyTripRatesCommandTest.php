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

test('passes when declared trips carry their declaration rate', function (): void {
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('All declared trips match their declaration.')
        ->assertSuccessful();
});

test('fails when a declared trip does not match its declaration', function (): void {
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->assertFailed();
});

test('ignores the rate applicable today when it superseded the declaration', function (): void {
    // The rate period was edited or superseded after the declaration was filed.
    // The trip still agrees with the declaration it was reimbursed under, so it
    // is settled history and must not be reported.
    Rate::factory()->create([
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'amount' => 0.9999,
        'omnium' => 0.0900,
    ]);

    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('All declared trips match their declaration.')
        ->assertSuccessful();
});

test('ignores undeclared trips', function (): void {
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
    $trip = declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.0100);

    $this->artisan('mileage:verify-trip-rates', ['--fix' => true])
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.0300);
});

test('--skip-omnium ignores omnium mismatches', function (): void {
    // Correct rate but wrong omnium.
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.9900);

    $this->artisan('mileage:verify-trip-rates', ['--skip-omnium' => true])
        ->assertSuccessful();

    // Without the flag the same trip fails on the omnium mismatch.
    $this->artisan('mileage:verify-trip-rates')
        ->assertFailed();
});

test('--skip-omnium still reports rate mismatches', function (): void {
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.9900);

    $this->artisan('mileage:verify-trip-rates', ['--skip-omnium' => true])
        ->assertFailed();
});

test('--fix with --skip-omnium corrects the rate but leaves omnium untouched', function (): void {
    $trip = declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.3000, 0.9900);

    $this->artisan('mileage:verify-trip-rates', ['--fix' => true, '--skip-omnium' => true])
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.9900);
});

test('passes when a non-omnium declaration carries a zero omnium', function (): void {
    // Beneficiary not entitled to omnium: stored omnium is 0, which is correct
    // even though the declaration carries a rate omnium. This must not be flagged.
    $declaration = Declaration::factory()->create([
        'omnium' => false,
        'rate' => 0.4000,
        'rate_omnium' => 0.0300,
    ]);
    declaredTripWithRate($this->user->id, $declaration->id, '2026-06-15', 0.4000, 0.0000);

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('All declared trips match their declaration.')
        ->assertSuccessful();
});

test('fails when a non-omnium declaration wrongly carries the rate omnium', function (): void {
    // Beneficiary not entitled to omnium but charged one anyway: expected 0.
    $declaration = Declaration::factory()->create([
        'omnium' => false,
        'rate' => 0.4000,
        'rate_omnium' => 0.0300,
    ]);
    declaredTripWithRate($this->user->id, $declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates')
        ->assertFailed();
});

test('--fix resets omnium to zero for a non-omnium declaration', function (): void {
    $declaration = Declaration::factory()->create([
        'omnium' => false,
        'rate' => 0.4000,
        'rate_omnium' => 0.0300,
    ]);
    $trip = declaredTripWithRate($this->user->id, $declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->artisan('mileage:verify-trip-rates', ['--fix' => true])
        ->assertSuccessful();

    $trip->refresh();

    expect((float) $trip->rate)->toBe(0.4000)
        ->and((float) $trip->omnium)->toBe(0.0000);
});

test('warns when the declaration a trip points at no longer exists', function (): void {
    declaredTripWithRate($this->user->id, $this->declaration->id, '2026-06-15', 0.4000, 0.0300);

    $this->declaration->delete();

    $this->artisan('mileage:verify-trip-rates')
        ->expectsOutputToContain('no longer exists')
        ->assertSuccessful();
});
