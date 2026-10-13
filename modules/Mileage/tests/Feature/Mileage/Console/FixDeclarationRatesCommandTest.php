<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;

beforeEach(function (): void {
    Rate::factory()->create([
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'amount' => 0.5000,
        'omnium' => 0.0062,
    ]);
    Rate::factory()->create([
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-31',
        'amount' => 0.5100,
        'omnium' => 0.0065,
    ]);
});

/**
 * A declaration filed at a stale rate, with one trip per given departure date.
 *
 * @param  array<int, string>  $departureDates
 */
function declarationFiledAtStaleRate(array $departureDates, bool $omnium = true): Declaration
{
    $declaration = Declaration::factory()->create([
        'omnium' => $omnium,
        'rate' => 0.4000,
        'rate_omnium' => 0.0030,
    ]);

    foreach ($departureDates as $departureDate) {
        Trip::factory()->create([
            'declaration_id' => $declaration->id,
            'departure_date' => $departureDate,
        ])->update(['rate' => 0.4000, 'omnium' => 0.0030]);
    }

    return $declaration;
}

test('puts the official rate on the declaration and its trips', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-10', '2026-06-20']);

    $this->artisan('mileage:fix-declaration-rates')
        ->expectsOutputToContain('Put the official rate on 1 declaration(s)')
        ->assertSuccessful();

    $declaration->refresh();

    expect((float) $declaration->rate)->toBe(0.5000)
        ->and((float) $declaration->rate_omnium)->toBe(0.0062)
        ->and($declaration->trips->map(fn (Trip $trip): float => (float) $trip->rate)->all())->toBe([0.5000, 0.5000])
        ->and($declaration->trips->map(fn (Trip $trip): float => (float) $trip->omnium)->all())->toBe([0.0062, 0.0062]);

    $this->artisan('mileage:verify-trip-rates')->assertSuccessful();
});

test('leaves the trip omnium at zero when the declaration has no omnium', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-10'], omnium: false);

    $this->artisan('mileage:fix-declaration-rates')->assertSuccessful();

    expect((float) $declaration->trips()->first()->omnium)->toBe(0.0);
});

test('writes nothing on a dry run', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-10']);

    $this->artisan('mileage:fix-declaration-rates', ['--dry-run' => true])
        ->expectsOutputToContain('1 declaration(s) would take the official rate')
        ->assertSuccessful();

    expect((float) $declaration->refresh()->rate)->toBe(0.4000)
        ->and((float) $declaration->trips()->first()->rate)->toBe(0.4000);
});

test('splits a declaration spanning two rate periods into one declaration per period', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-25', '2026-07-05', '2026-07-20']);
    $declaration->forceFill(['created_at' => '2026-08-01 10:00:00'])->saveQuietly();

    $this->artisan('mileage:fix-declaration-rates')
        ->expectsOutputToContain('Split 1 declaration(s)')
        ->assertSuccessful();

    $declaration->refresh();
    $copy = Declaration::query()->whereKeyNot($declaration->id)->sole();

    expect((float) $declaration->rate)->toBe(0.5000)
        ->and($declaration->trips()->pluck('departure_date')->map->format('Y-m-d')->all())->toBe(['2026-06-25'])
        ->and((float) $copy->rate)->toBe(0.5100)
        ->and((float) $copy->rate_omnium)->toBe(0.0065)
        ->and($copy->last_name)->toBe($declaration->last_name)
        ->and($copy->created_at->format('Y-m-d H:i'))->toBe('2026-08-01 10:00')
        ->and($copy->trips()->count())->toBe(2)
        ->and($copy->trips->every(fn (Trip $trip): bool => (float) $trip->rate === 0.5100))->toBeTrue();

    $this->artisan('mileage:verify-trip-rates')->assertSuccessful();
});

test('does not split a declaration on a dry run', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-25', '2026-07-05']);

    $this->artisan('mileage:fix-declaration-rates', ['--dry-run' => true])
        ->expectsOutputToContain('1 declaration(s) span several rate periods and would be split')
        ->assertSuccessful();

    expect(Declaration::query()->count())->toBe(1)
        ->and($declaration->trips()->count())->toBe(2)
        ->and((float) $declaration->refresh()->rate)->toBe(0.4000);
});

test('lists a declaration holding a trip no rate period covers for manual handling', function (): void {
    Rate::factory()->create([
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-30',
        'amount' => 0.5200,
        'omnium' => 0.0070,
    ]);
    $declaration = declarationFiledAtStaleRate(['2026-09-10', '2026-10-05']);

    $this->artisan('mileage:fix-declaration-rates')
        ->expectsOutputToContain('must be corrected by hand')
        ->assertSuccessful();

    expect(Declaration::query()->count())->toBe(1)
        ->and((float) $declaration->refresh()->rate)->toBe(0.4000);
});

test('ignores trips departing before the --since year', function (): void {
    $declaration = declarationFiledAtStaleRate(['2026-06-10']);

    $this->artisan('mileage:fix-declaration-rates', ['--since' => 2027])
        ->expectsOutputToContain('Every declaration carries the official rate.')
        ->assertSuccessful();

    expect((float) $declaration->refresh()->rate)->toBe(0.4000);
});
