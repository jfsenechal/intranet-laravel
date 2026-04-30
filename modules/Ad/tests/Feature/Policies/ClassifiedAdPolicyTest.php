<?php

declare(strict_types=1);

use AcMarche\Ad\Enums\RolesEnum;
use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Security\Models\Role;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('allows any user to view any ad', function (): void {
    expect(auth()->user()->can('viewAny', ClassifiedAd::class))->toBeTrue();
});

it('allows any user to view a ad', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('view', $classifiedAd))->toBeTrue();
});

it('allows any user to create ad', function (): void {
    expect(auth()->user()->can('create', ClassifiedAd::class))->toBeTrue();
});

it('allows an administrator to update ad', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('update', $classifiedAd))->toBeTrue();
});

it('allows a user with ROLE_NEWS_ADMIN to update ad', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_AD_ADMIN->value]);
    auth()->user()->roles()->attach($role);
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('update', $classifiedAd))->toBeTrue();
});

it('allows the author to update their own ad', function (): void {
    $user = auth()->user();
    $classifiedAd = ClassifiedAd::factory()->create(['user_add' => $user->username]);

    expect($user->can('update', $classifiedAd))->toBeTrue();
});

it('denies a regular user to update ad they did not create', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();
    $classifiedAd->updateQuietly(['user_add' => 'other-user']);

    expect(auth()->user()->can('update', $classifiedAd))->toBeFalse();
});

it('allows an administrator to delete ad', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('delete', $classifiedAd))->toBeTrue();
});

it('allows the author to delete their own ad', function (): void {
    $user = auth()->user();
    $classifiedAd = ClassifiedAd::factory()->create(['user_add' => $user->username]);

    expect($user->can('delete', $classifiedAd))->toBeTrue();
});

it('denies a regular user to delete ad they did not create', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();
    $classifiedAd->updateQuietly(['user_add' => 'other-user']);

    expect(auth()->user()->can('delete', $classifiedAd))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('restore', $classifiedAd))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    expect(auth()->user()->can('forceDelete', $classifiedAd))->toBeFalse();
});
