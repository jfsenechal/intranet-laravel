<?php

declare(strict_types=1);

use AcMarche\Agent\Models\Profile;
use AcMarche\Agent\Models\ProfileHardware;
use AcMarche\Agent\Models\ProfilePhone;
use AcMarche\Agent\Models\Share;
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

it('prunes a profile that has related hardware, phone and shares', function (): void {
    $profile = Profile::factory()->create(['employee_id' => 999999]);
    $profile->hardware()->save(new ProfileHardware);
    $profile->phone()->save(new ProfilePhone);
    $profile->shares()->save(new Share(['shared_by' => 'a', 'shared_for' => 'b']));

    $this->artisan('agent:prune-profiles')->assertSuccessful();

    expect(Profile::query()->whereKey($profile->getKey())->exists())->toBeFalse()
        ->and(ProfileHardware::query()->where('profile_id', $profile->getKey())->exists())->toBeFalse()
        ->and(ProfilePhone::query()->where('profile_id', $profile->getKey())->exists())->toBeFalse()
        ->and(Share::query()->where('profile_id', $profile->getKey())->exists())->toBeFalse();
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
