<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Employee;
use Illuminate\Support\Carbon;

it('clears is_new_hire for employees hired more than one month ago', function (): void {
    $oldHire = Employee::factory()->create([
        'is_new_hire' => true,
        'hired_at' => Carbon::today()->subMonths(2),
    ]);

    $this->artisan('hrm:expire-new-hires')->assertSuccessful();

    expect($oldHire->refresh()->is_new_hire)->toBeFalse();
});

it('keeps is_new_hire on employees hired within the last month', function (): void {
    $recentHire = Employee::factory()->create([
        'is_new_hire' => true,
        'hired_at' => Carbon::today()->subDays(10),
    ]);

    $this->artisan('hrm:expire-new-hires')->assertSuccessful();

    expect($recentHire->refresh()->is_new_hire)->toBeTrue();
});

it('clears is_new_hire on the exact one month boundary', function (): void {
    $boundaryHire = Employee::factory()->create([
        'is_new_hire' => true,
        'hired_at' => Carbon::today()->subMonth(),
    ]);

    $this->artisan('hrm:expire-new-hires')->assertSuccessful();

    expect($boundaryHire->refresh()->is_new_hire)->toBeFalse();
});

it('ignores employees with a null hired_at', function (): void {
    $noHireDate = Employee::factory()->create([
        'is_new_hire' => true,
        'hired_at' => null,
    ]);

    $this->artisan('hrm:expire-new-hires')->assertSuccessful();

    expect($noHireDate->refresh()->is_new_hire)->toBeTrue();
});
