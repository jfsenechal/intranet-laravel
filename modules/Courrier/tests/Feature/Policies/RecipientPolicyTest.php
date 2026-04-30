<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Security\Models\Role;

it('allows an administrator to view any recipients', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('viewAny', Recipient::class))->toBeTrue();
});

it('allows a user with courrier admin role to view any recipients', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('viewAny', Recipient::class))->toBeTrue();
});

it('denies a regular user to view any recipients', function (): void {
    expect(auth()->user()->can('viewAny', Recipient::class))->toBeFalse();
});

it('allows an administrator to create a recipient', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('create', Recipient::class))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_VILLE_ADMIN to create a recipient', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('create', Recipient::class))->toBeTrue();
});

it('denies a regular user to create a recipient', function (): void {
    expect(auth()->user()->can('create', Recipient::class))->toBeFalse();
});

it('allows an administrator to update a recipient', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('update', $recipient))->toBeTrue();
});

it('denies a regular user to update a recipient', function (): void {
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('update', $recipient))->toBeFalse();
});

it('allows an administrator to delete a recipient', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('delete', $recipient))->toBeTrue();
});

it('denies a regular user to delete a recipient', function (): void {
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('delete', $recipient))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('restore', $recipient))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $recipient = Recipient::factory()->create();

    expect(auth()->user()->can('forceDelete', $recipient))->toBeFalse();
});
