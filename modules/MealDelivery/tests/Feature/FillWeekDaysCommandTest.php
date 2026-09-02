<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Week;

it('completes a five day week to seven days', function (): void {
    $week = Week::create([
        'first_day' => '2026-09-07',
        'days' => ['2026-09-07', '2026-09-08', '2026-09-09', '2026-09-10', '2026-09-11'],
    ]);

    $this->artisan('meal-delivery:fill-week-days')->assertSuccessful();

    expect($week->refresh()->days)->toBe([
        '2026-09-07',
        '2026-09-08',
        '2026-09-09',
        '2026-09-10',
        '2026-09-11',
        '2026-09-12',
        '2026-09-13',
    ]);
});

it('keeps a day stored outside the monday to sunday range', function (): void {
    $week = Week::create([
        'first_day' => '2026-09-07',
        'days' => ['2026-09-07', '2026-09-14'],
    ]);

    $this->artisan('meal-delivery:fill-week-days')->assertSuccessful();

    expect($week->refresh()->days)
        ->toHaveCount(8)
        ->toContain('2026-09-14');
});

it('leaves a week that already has its seven days untouched', function (): void {
    $days = [
        '2026-09-07',
        '2026-09-08',
        '2026-09-09',
        '2026-09-10',
        '2026-09-11',
        '2026-09-12',
        '2026-09-13',
    ];

    $week = Week::create(['first_day' => '2026-09-07', 'days' => $days]);

    $this->artisan('meal-delivery:fill-week-days')
        ->expectsOutputToContain('0 week(s) completed')
        ->assertSuccessful();

    expect($week->refresh()->days)->toBe($days);
});

it('does not save anything on a dry run', function (): void {
    $week = Week::create([
        'first_day' => '2026-09-07',
        'days' => ['2026-09-07'],
    ]);

    $this->artisan('meal-delivery:fill-week-days', ['--dry-run' => true])
        ->expectsOutputToContain('1 week(s) would be completed')
        ->assertSuccessful();

    expect($week->refresh()->days)->toBe(['2026-09-07']);
});

it('skips archived weeks when asked to', function (): void {
    $archived = Week::create([
        'first_day' => '2026-09-07',
        'days' => ['2026-09-07'],
        'is_archived' => true,
    ]);

    $this->artisan('meal-delivery:fill-week-days', ['--skip-archived' => true])->assertSuccessful();

    expect($archived->refresh()->days)->toBe(['2026-09-07']);
});

it('ignores the weeks starting on or before the cutoff', function (): void {
    $before = Week::create([
        'first_day' => '2026-08-31',
        'days' => ['2026-08-31'],
    ]);

    $this->artisan('meal-delivery:fill-week-days')->assertSuccessful();

    expect($before->refresh()->days)->toBe(['2026-08-31']);
});

it('accepts another cutoff through the from option', function (): void {
    $week = Week::create([
        'first_day' => '2026-08-31',
        'days' => ['2026-08-31'],
    ]);

    $this->artisan('meal-delivery:fill-week-days', ['--from' => '2026-01-01'])->assertSuccessful();

    expect($week->refresh()->days)->toHaveCount(7);
});
