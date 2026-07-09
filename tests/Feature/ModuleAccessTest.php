<?php

declare(strict_types=1);

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    Module::query()->delete();
});

it('includes public modules', function (): void {
    $publicModule = Module::factory()->create(['id' => 100, 'is_public' => true]);

    $modules = Module::accessibleTo(auth()->user())->get();

    expect($modules->pluck('id'))->toContain($publicModule->id);
});

it('excludes non-public modules when the user has no role on them', function (): void {
    $privateModule = Module::factory()->create(['id' => 100, 'is_public' => false]);
    Role::factory()->create(['module_id' => $privateModule->id]);

    $modules = Module::accessibleTo(auth()->user())->get();

    expect($modules->pluck('id'))->not->toContain($privateModule->id);
});

it('includes non-public modules when the user has at least one role on them', function (): void {
    $privateModule = Module::factory()->create(['id' => 100, 'is_public' => false]);
    $role = Role::factory()->create(['module_id' => $privateModule->id]);

    /** @var User $user */
    $user = auth()->user();
    $user->roles()->attach($role);

    $modules = Module::accessibleTo($user)->get();

    expect($modules->pluck('id'))->toContain($privateModule->id);
});

it('returns all non-skipped modules for administrators regardless of roles', function (): void {
    $publicModule = Module::factory()->create(['id' => 100, 'is_public' => true]);
    $privateModule = Module::factory()->create(['id' => 101, 'is_public' => false]);

    /** @var User $user */
    $user = auth()->user();
    $user->update(['is_administrator' => true]);

    $modules = Module::accessibleTo($user)->get();

    expect($modules->pluck('id'))
        ->toContain($publicModule->id)
        ->toContain($privateModule->id);
});

it('skips modules listed in MODULES_TO_SKIP even when public', function (): void {
    $skippedId = Module::MODULES_TO_SKIP[0];
    Module::factory()->create(['id' => $skippedId, 'is_public' => true]);

    $modules = Module::accessibleTo(auth()->user())->get();

    expect($modules->pluck('id'))->not->toContain($skippedId);
});

it('reports a module as migrated only when it holds a url', function (): void {
    $external = Module::factory()->create(['is_external' => true, 'url' => 'https://example.test']);
    $internalWithUrl = Module::factory()->create(['is_external' => false, 'url' => '/hrm/employees']);
    $internalWithoutUrl = Module::factory()->create(['is_external' => false, 'url' => '']);

    expect($external->migrated)->toBeTrue()
        ->and($internalWithUrl->migrated)->toBeTrue()
        ->and($internalWithoutUrl->migrated)->toBeFalse();
});
