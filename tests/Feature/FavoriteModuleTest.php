<?php

declare(strict_types=1);

use AcMarche\App\Handler\FavoriteModuleHandler;
use AcMarche\Security\Models\Module;
use App\Models\User;
use Livewire\Livewire;

/**
 * Persist a module with an explicit id (the modules table uses legacy ids).
 */
function makeFavoritableModule(int $id, array $attributes = []): Module
{
    $module = Module::factory()->make(array_merge([
        'is_external' => true,
        'is_public' => true,
    ], $attributes));
    $module->id = $id;
    $module->save();

    return $module;
}

it('toggles a module in and out of favorites', function (): void {
    $user = User::factory()->create();
    $module = makeFavoritableModule(100);

    expect($user->hasFavoriteModule($module->id))->toBeFalse();

    expect($user->toggleFavoriteModule($module->id))->toBeTrue();
    expect($user->fresh()->hasFavoriteModule($module->id))->toBeTrue();

    expect($user->toggleFavoriteModule($module->id))->toBeFalse();
    expect($user->fresh()->hasFavoriteModule($module->id))->toBeFalse();
});

it('returns only the user favorite modules when set', function (): void {
    $user = User::factory()->create(['is_administrator' => true]);
    $favorite = makeFavoritableModule(101);
    makeFavoritableModule(102);

    $user->favoriteModules()->attach($favorite->id);

    expect(FavoriteModuleHandler::getFavoriteModules($user)->pluck('id')->all())
        ->toBe([101]);
});

it('falls back to the default favorites when the user has none', function (): void {
    $user = User::factory()->create(['is_administrator' => true]);
    foreach (FavoriteModuleHandler::DEFAULT_FAVORITE_IDS as $id) {
        makeFavoritableModule($id);
    }

    expect(FavoriteModuleHandler::getFavoriteModules($user)->pluck('id')->all())
        ->toBe(FavoriteModuleHandler::DEFAULT_FAVORITE_IDS);
});

it('lets a user favorite a module from the launcher', function (): void {
    $user = User::factory()->create(['is_administrator' => true]);
    $module = makeFavoritableModule(103);

    $this->actingAs($user);

    Livewire::test('modules-launcher')
        ->call('toggleFavorite', $module->id)
        ->assertSet('favoriteModuleIds', [$module->id]);

    expect($user->fresh()->hasFavoriteModule($module->id))->toBeTrue();
});
