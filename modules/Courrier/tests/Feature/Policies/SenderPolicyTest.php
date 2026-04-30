<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Sender;
use AcMarche\Security\Models\Role;

it('allows an administrator to view any senders', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('viewAny', Sender::class))->toBeTrue();
});

it('allows a user with courrier admin role to view any senders', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('viewAny', Sender::class))->toBeTrue();
});

it('denies a regular user to view any senders', function (): void {
    expect(auth()->user()->can('viewAny', Sender::class))->toBeFalse();
});

it('allows an administrator to create a sender', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('create', Sender::class))->toBeTrue();
});

it('allows a user with ROLE_INDICATEUR_CPAS_ADMIN to create a sender', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('create', Sender::class))->toBeTrue();
});

it('denies a regular user to create a sender', function (): void {
    expect(auth()->user()->can('create', Sender::class))->toBeFalse();
});

it('allows an administrator to update a sender', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('update', $sender))->toBeTrue();
});

it('denies a regular user to update a sender', function (): void {
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('update', $sender))->toBeFalse();
});

it('allows an administrator to delete a sender', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('delete', $sender))->toBeTrue();
});

it('denies a regular user to delete a sender', function (): void {
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('delete', $sender))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('restore', $sender))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $sender = Sender::factory()->create();

    expect(auth()->user()->can('forceDelete', $sender))->toBeFalse();
});
