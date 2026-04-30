<?php

declare(strict_types=1);

use AcMarche\News\Enums\RolesEnum;
use AcMarche\News\Models\Category;
use AcMarche\Security\Models\Role;

it('allows any user to view any categories', function (): void {
    $user = auth()->user();

    expect($user->can('viewAny', Category::class))->toBeTrue();
});

it('allows any user to view a category', function (): void {
    $category = Category::factory()->create();

    expect(auth()->user()->can('view', $category))->toBeTrue();
});

it('allows an administrator to create a category', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('create', Category::class))->toBeTrue();
});

it('allows a user with ROLE_NEWS_ADMIN to create a category', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_NEWS_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('create', Category::class))->toBeTrue();
});

it('denies a regular user to create a category', function (): void {
    expect(auth()->user()->can('create', Category::class))->toBeFalse();
});

it('allows an administrator to update a category', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $category = Category::factory()->create();

    expect(auth()->user()->can('update', $category))->toBeTrue();
});

it('denies a regular user to update a category', function (): void {
    $category = Category::factory()->create();

    expect(auth()->user()->can('update', $category))->toBeFalse();
});

it('allows an administrator to delete a category', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $category = Category::factory()->create();

    expect(auth()->user()->can('delete', $category))->toBeTrue();
});

it('denies a regular user to delete a category', function (): void {
    $category = Category::factory()->create();

    expect(auth()->user()->can('delete', $category))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $category = Category::factory()->create();

    expect(auth()->user()->can('restore', $category))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $category = Category::factory()->create();

    expect(auth()->user()->can('forceDelete', $category))->toBeFalse();
});
