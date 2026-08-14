<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ServiceRepository
{
    public static function findAllActiveOrdered(): Collection
    {
        return Service::query()->orderBy('name')->pluck('name', 'id');
    }

    /**
     * Services whose name or initials match the search term. Services are
     * commonly referred to by their initials, so both are searched.
     *
     * @return Collection<int, string> keyed by service id
     */
    public static function searchByNameOrInitials(string $search, int $limit = 50): Collection
    {
        return Service::query()
            ->where(fn (Builder $query): Builder => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('initials', 'like', "%{$search}%"))
            ->orderBy('name')
            ->limit($limit)
            ->pluck('name', 'id');
    }
}
