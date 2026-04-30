<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Security\Models\Role;

it('allows an administrator to access courrier-index gate', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('courrier-index'))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_VILLE_INDEX to access courrier-index gate', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('courrier-index'))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_CPAS_INDEX to access courrier-index gate', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('courrier-index'))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_BOURGMESTRE_INDEX to access courrier-index gate', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('courrier-index'))->toBeTrue();
});

it('denies a regular user to access courrier-index gate', function (): void {
    expect(auth()->user()->can('courrier-index'))->toBeFalse();
});

it('allows an administrator to access courrier-administrator gate', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('courrier-administrator'))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_VILLE_ADMIN to access courrier-administrator gate', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('courrier-administrator'))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_CPAS_ADMIN to access courrier-administrator gate', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('courrier-administrator'))->toBeTrue();
});

it('denies a regular user to access courrier-administrator gate', function (): void {
    expect(auth()->user()->can('courrier-administrator'))->toBeFalse();
});
