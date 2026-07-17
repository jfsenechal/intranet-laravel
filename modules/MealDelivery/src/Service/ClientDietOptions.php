<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Diet;
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
}
