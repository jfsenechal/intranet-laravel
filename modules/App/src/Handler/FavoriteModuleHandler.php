<?php

declare(strict_types=1);

namespace AcMarche\App\Handler;

use AcMarche\Security\Handler\MigrationHandler;
use AcMarche\Security\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;

final class FavoriteModuleHandler
{
    /**
     * Modules shown on the homepage when the user has not picked any favorite yet.
     *
     * @var list<int>
     */
    public const array DEFAULT_FAVORITE_IDS = [9, 30, 15, 42];

    /**
     * Resolve the favorite modules to display for the given user, falling back to
     * the default set when the user has none. Only modules the user can access are
     * returned, with their url and migration status resolved.
     *
     * @return Collection<int, Module>
     */
    public static function getFavoriteModules(?User $user = null): Collection
    {
        $user ??= auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        $favoriteIds = self::favoriteIds($user);
        if ($favoriteIds === []) {
            $favoriteIds = self::DEFAULT_FAVORITE_IDS;
        }

        return MigrationHandler::getAllModules($user)
            ->whereIn('id', $favoriteIds)
            ->sortBy(fn (Module $module): int|false => array_search($module->id, $favoriteIds, true))
            ->values();
    }

    /**
     * The ids of the modules the user explicitly marked as favorite (no fallback).
     *
     * @return list<int>
     */
    public static function favoriteIds(?User $user = null): array
    {
        $user ??= auth()->user();
        if (! $user instanceof User) {
            return [];
        }

        return $user->favoriteModules()
            ->pluck('modules.id')
            ->map(fn (int $id): int => $id)
            ->all();
    }
}
