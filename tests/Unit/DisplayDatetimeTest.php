<?php

declare(strict_types=1);

use Carbon\Carbon;
use Filament\Support\Facades\FilamentTimezone;

it('keeps storing timestamps in UTC', function (): void {
    expect(config('app.timezone'))->toBe('UTC');
});

it('converts a UTC timestamp to the display timezone', function (): void {
    config()->set('app.display_timezone', 'Europe/Brussels');

    expect(display_datetime(Carbon::parse('2026-08-04 06:24:00', 'UTC'), 'd/m/Y à H:i'))
        ->toBe('04/08/2026 à 08:24');
});

it('honours the configured display timezone', function (): void {
    config()->set('app.display_timezone', 'UTC');

    expect(display_datetime(Carbon::parse('2026-08-04 06:24:00', 'UTC'), 'H:i'))
        ->toBe('06:24');
});

it('returns null when there is no date', function (): void {
    expect(display_datetime(null))->toBeNull();
});

it('does not mutate the given date', function (): void {
    config()->set('app.display_timezone', 'Europe/Brussels');
    $date = Carbon::parse('2026-08-04 06:24:00', 'UTC');

    display_datetime($date);

    expect($date->format('H:i'))->toBe('06:24');
});

it('defaults to the day and time format', function (): void {
    config()->set('app.display_timezone', 'Europe/Brussels');

    expect(display_datetime(Carbon::parse('2026-08-04 06:24:00', 'UTC')))
        ->toBe('04/08/2026 08:24');
});

it('applies the display timezone to Filament components', function (): void {
    expect(FilamentTimezone::get())->toBe(config('app.display_timezone'));
});
