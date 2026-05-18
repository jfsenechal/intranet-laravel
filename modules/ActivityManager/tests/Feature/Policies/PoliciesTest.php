<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Models\Activite;
use AcMarche\ActivityManager\Models\Cours;
use AcMarche\ActivityManager\Models\Membre;
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
    expect(Gate::forUser($this->admin)->allows('viewAny', Activite::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Activite::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Activite::class))->toBeFalse();
});

it('grants create on Activite to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Activite::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Activite::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Activite::class))->toBeFalse();
});

it('grants viewAny on Cours to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Cours::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Cours::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Cours::class))->toBeFalse();
});

it('grants create on Cours to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Cours::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Cours::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Cours::class))->toBeFalse();
});

it('grants viewAny on Membre to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewAny', Membre::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('viewAny', Membre::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('viewAny', Membre::class))->toBeFalse();
});

it('grants create on Membre to role holders and admins', function (): void {
    expect(Gate::forUser($this->admin)->allows('create', Membre::class))->toBeTrue();
    expect(Gate::forUser($this->mdaAdmin)->allows('create', Membre::class))->toBeTrue();
    expect(Gate::forUser($this->stranger)->allows('create', Membre::class))->toBeFalse();
});

it('forbids restore and forceDelete for everyone', function (): void {
    expect(Gate::forUser($this->admin)->allows('restore', Activite::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Activite::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('restore', Cours::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Cours::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('restore', Membre::class))->toBeFalse();
    expect(Gate::forUser($this->admin)->allows('forceDelete', Membre::class))->toBeFalse();
});
