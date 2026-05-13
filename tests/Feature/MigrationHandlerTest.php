<?php

declare(strict_types=1);

use AcMarche\Security\Handler\MigrationHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;

beforeEach(function (): void {
    Module::query()->delete();
});

it('includes public modules', function (): void {
    $publicModule = Module::factory()->create(['is_public' => true]);

    $modules = MigrationHandler::getAllModules();

    expect($modules->pluck('id'))->toContain($publicModule->id);
});

it('excludes non-public modules when the user has no role on them', function (): void {
    $privateModule = Module::factory()->create(['is_public' => false]);
    Role::factory()->create(['module_id' => $privateModule->id]);

    $modules = MigrationHandler::getAllModules();

    expect($modules->pluck('id'))->not->toContain($privateModule->id);
});

it('includes non-public modules when the user has at least one role on them', function (): void {
    $privateModule = Module::factory()->create(['is_public' => false]);
    $role = Role::factory()->create(['module_id' => $privateModule->id]);

    /** @var User $user */
    $user = auth()->user();
    $user->roles()->attach($role);

    $modules = MigrationHandler::getAllModules();

    expect($modules->pluck('id'))->toContain($privateModule->id);
});

it('returns an empty collection when no user is authenticated', function (): void {
    Module::factory()->create(['is_public' => true]);

    auth()->logout();

    expect(MigrationHandler::getAllModules())->toBeEmpty();
});

it('returns all non-skipped modules for administrators regardless of roles', function (): void {
    $publicModule = Module::factory()->create(['is_public' => true]);
    $privateModule = Module::factory()->create(['is_public' => false]);

    /** @var User $user */
    $user = auth()->user();
    $user->update(['is_administrator' => true]);

    $modules = MigrationHandler::getAllModules();

    expect($modules->pluck('id'))
        ->toContain($publicModule->id)
        ->toContain($privateModule->id);
});

it('skips modules listed in modules_to_skip even when public', function (): void {
    $skippedId = MigrationHandler::modules_to_skip[0];
    Module::factory()->create(['id' => $skippedId, 'is_public' => true]);

    $modules = MigrationHandler::getAllModules();

    expect($modules->pluck('id'))->not->toContain($skippedId);
});
