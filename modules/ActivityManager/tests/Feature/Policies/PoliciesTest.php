<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->mdaAdmin = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin->roles()->attach($role);

    $this->stranger = User::factory()->create();
});

it('grants viewAny on Activite to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Activity::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Activity::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Activity::class))->toBeFalse();
});

it('grants create on Activite to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Activity::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Activity::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Activity::class))->toBeFalse();
});

it('grants viewAny on Cours to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Schedule::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Schedule::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Schedule::class))->toBeFalse();
});

it('grants create on Cours to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Schedule::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Schedule::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Schedule::class))->toBeFalse();
});

it('grants viewAny on Membre to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Member::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Member::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Member::class))->toBeFalse();
});

it('grants create on Membre to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Member::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Member::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Member::class))->toBeFalse();
});

it('forbids restore and forceDelete for everyone', function (): void {
    expect(Gate::forUser($this->admin)->allows('restore', Activity::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Activity::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('restore', Schedule::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Schedule::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('restore', Member::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Member::class))->toBeFalse();
});
