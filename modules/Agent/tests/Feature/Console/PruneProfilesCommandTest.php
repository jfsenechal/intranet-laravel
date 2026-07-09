<?php

declare(strict_types=1);

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;

it('prunes a profile whose employee no longer exists in HRM', function (): void {
    $profile = Profile::factory()->create(['employee_id' => 999999]);

    $this->artisan('agent:prune-profiles')->assertSuccessful();

    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeFalse();
});

it('prunes a profile with a null employee_id', function (): void {
    $profile = Profile::factory()->create(['employee_id' => null]);

    $this->artisan('agent:prune-profiles')->assertSuccessful();

    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeFalse();
});

it('keeps a profile whose employee still exists in HRM', function (): void {
    $employee = Employee::factory()->create();
    $profile = Profile::factory()->create(['employee_id' => $employee->getKey()]);

    $this->artisan('agent:prune-profiles')->assertSuccessful();

    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeTrue();
});

it('does not delete anything in dry-run mode', function (): void {
    $profile = Profile::factory()->create(['employee_id' => 999999]);

    $this->artisan('agent:prune-profiles', ['--dry-run' => true])->assertSuccessful();

    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeTrue();
});
