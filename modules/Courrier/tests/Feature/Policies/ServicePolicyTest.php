<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;

it('allows an administrator to view any services', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('viewAny', Service::class))->toBeTrue();
});

it('allows a user with courrier admin role to view any services', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('viewAny', Service::class))->toBeTrue();
});

it('denies a regular user to view any services', function (): void {
    expect(auth()->user()->can('viewAny', Service::class))->toBeFalse();
});

it('allows an administrator to create a service', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('create', Service::class))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_BOURGMESTRE_ADMIN to create a service', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('create', Service::class))->toBeTrue();
});

it('denies a regular user to create a service', function (): void {
    expect(auth()->user()->can('create', Service::class))->toBeFalse();
});

it('allows an administrator to update a service', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $service = Service::factory()->create();

    expect(auth()->user()->can('update', $service))->toBeTrue();
});

it('denies a regular user to update a service', function (): void {
    $service = Service::factory()->create();

    expect(auth()->user()->can('update', $service))->toBeFalse();
});

it('allows an administrator to delete a service', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $service = Service::factory()->create();

    expect(auth()->user()->can('delete', $service))->toBeTrue();
});

it('denies a regular user to delete a service', function (): void {
    $service = Service::factory()->create();

    expect(auth()->user()->can('delete', $service))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $service = Service::factory()->create();

    expect(auth()->user()->can('restore', $service))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $service = Service::factory()->create();

    expect(auth()->user()->can('forceDelete', $service))->toBeFalse();
});
