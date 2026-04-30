<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;

it('allows any user to view any incoming mails', function (): void {
    expect(auth()->user()->can('viewAny', IncomingMail::class))->toBeTrue();
});

it('allows an administrator to view an incoming mail', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $mail = IncomingMail::factory()->create();

    expect(auth()->user()->can('view', $mail))->toBeTrue();
});

it('allows a recipient of the mail to view it', function (): void {
    $user = auth()->user();
    $mail = IncomingMail::factory()->create();
    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $mail->recipients()->attach($recipient->id);

    expect($user->can('view', $mail))->toBeTrue();
});

it('allows a member of a linked service to view the mail', function (): void {
    $user = auth()->user();
    $mail = IncomingMail::factory()->create();
    $service = Service::factory()->create();
    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $recipient->services()->attach($service->id);
    $mail->services()->attach($service->id);

    expect($user->can('view', $mail))->toBeTrue();
});

it('denies a regular user to view an incoming mail they are not linked to', function (): void {
    $mail = IncomingMail::factory()->create();

    expect(auth()->user()->can('view', $mail))->toBeFalse();
});

it('allows an administrator to create an incoming mail', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('create', IncomingMail::class))->toBeTrue();
});

it('allows a user with courrier admin role to create an incoming mail', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('create', IncomingMail::class))->toBeTrue();
});

it('denies a regular user to create an incoming mail', function (): void {
    expect(auth()->user()->can('create', IncomingMail::class))->toBeFalse();
});

it('allows an administrator to update an incoming mail', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('update', IncomingMail::factory()->create()))->toBeTrue();
});

it('denies a regular user to update an incoming mail', function (): void {
    expect(auth()->user()->can('update', IncomingMail::factory()->create()))->toBeFalse();
});

it('allows an administrator to delete an incoming mail', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('delete', IncomingMail::factory()->create()))->toBeTrue();
});

it('denies a regular user to delete an incoming mail', function (): void {
    expect(auth()->user()->can('delete', IncomingMail::factory()->create()))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $mail = IncomingMail::factory()->create();

    expect(auth()->user()->can('restore', $mail))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $mail = IncomingMail::factory()->create();

    expect(auth()->user()->can('forceDelete', $mail))->toBeFalse();
});
