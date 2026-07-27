<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Menu;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resolves the diets of a client as select options, memoized for the current
 * request so that repeating the same select across meal rows costs one query.
 *
 * Registered as a scoped binding: the memo must not outlive the request, as
 * Octane keeps the container alive between them.
 */
final class ClientDietOptions
{
    /**
     * @var array<int, array<int, string>>
     */
    private array $optionsByClient = [];

    /**
     * @var array<int, array<int, int>>
     */
    private array $acceptedByClient = [];

    /**
     * @return array<int, string>
     */
    public function forClient(int $clientId): array
    {
        return $this->optionsByClient[$clientId] ??= Diet::query()
            ->whereHas('clients', fn (Builder $query) => $query->whereKey($clientId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * The diet ids a client may legitimately hold on a menu: the ones it is
     * linked to, plus the ones its own menus already carry. A diet unlinked
     * from a client after the fact stays valid on the orders that use it,
     * without ever being offered again.
     *
     * @return array<int, int>
     */
    public function acceptedForClient(int $clientId): array
    {
        return $this->acceptedByClient[$clientId] ??= array_values(array_unique([
            ...array_map(intval(...), array_keys($this->forClient($clientId))),
            ...$this->dietIdsOnMenusOfClient($clientId),
        ]));
    }

    /**
     * @return array<int, int>
     */
    private function dietIdsOnMenusOfClient(int $clientId): array
    {
        return Menu::query()
            ->join('meals', 'meals.id', '=', 'menus.meal_id')
            ->join('orders', 'orders.id', '=', 'meals.order_id')
            ->join('diet_menu', 'diet_menu.menu_id', '=', 'menus.id')
            ->where('orders.client_id', $clientId)
            ->distinct()
            ->pluck('diet_menu.diet_id')
            ->map(intval(...))
            ->all();
    }
}
