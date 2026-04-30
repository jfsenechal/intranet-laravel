<?php

declare(strict_types=1);

use AcMarche\News\Enums\RolesEnum;
use AcMarche\News\Models\News;
use AcMarche\Security\Models\Role;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('allows any user to view any news', function (): void {
    expect(auth()->user()->can('viewAny', News::class))->toBeTrue();
});

it('allows any user to view a news', function (): void {
    $news = News::factory()->create();

    expect(auth()->user()->can('view', $news))->toBeTrue();
});

it('allows any user to create news', function (): void {
    expect(auth()->user()->can('create', News::class))->toBeTrue();
});

it('allows an administrator to update news', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $news = News::factory()->create();

    expect(auth()->user()->can('update', $news))->toBeTrue();
});

it('allows a user with ROLE_NEWS_ADMIN to update news', function (): void {
    $role = Role::create(['name' => RolesEnum::ROLE_NEWS_ADMIN->value]);
    auth()->user()->roles()->attach($role);
    $news = News::factory()->create();

    expect(auth()->user()->can('update', $news))->toBeTrue();
});

it('allows the author to update their own news', function (): void {
    $user = auth()->user();
    $news = News::factory()->create(['user_add' => $user->username]);

    expect($user->can('update', $news))->toBeTrue();
});

it('denies a regular user to update news they did not create', function (): void {
    $news = News::factory()->create();
    $news->updateQuietly(['user_add' => 'other-user']);

    expect(auth()->user()->can('update', $news))->toBeFalse();
});

it('allows an administrator to delete news', function (): void {
    auth()->user()->update(['is_administrator' => true]);
    $news = News::factory()->create();

    expect(auth()->user()->can('delete', $news))->toBeTrue();
});

it('allows the author to delete their own news', function (): void {
    $user = auth()->user();
    $news = News::factory()->create(['user_add' => $user->username]);

    expect($user->can('delete', $news))->toBeTrue();
});

it('denies a regular user to delete news they did not create', function (): void {
    $news = News::factory()->create();
    $news->updateQuietly(['user_add' => 'other-user']);

    expect(auth()->user()->can('delete', $news))->toBeFalse();
});

it('denies restore for any user', function (): void {
    $news = News::factory()->create();

    expect(auth()->user()->can('restore', $news))->toBeFalse();
});

it('denies force delete for any user', function (): void {
    $news = News::factory()->create();

    expect(auth()->user()->can('forceDelete', $news))->toBeFalse();
});
