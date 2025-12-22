<?php

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\Declaration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use LaravelIdea\Helper\AcMarche\Mileage\Models\_IH_Declaration_C;

final class DeclarationRepository
{
    public static function getByUser(Builder $query): Builder
    {
        $user = auth()->user();
        $username = $user->username;
        // todo remove
        $username = 'aaguirre';

        return $query->where('user_add', '=', $username);
    }

    public static function findAll(): Builder
    {
        return Declaration::query()->with('trips');
    }

    /**
     * Retrieve a collection of declarations filtered by the specified year, departments, and omnium flag.
     *
     * @param int $year The year to filter the declarations by.
     * @param array $departments An array of department identifiers to filter the declarations. Defaults to an empty array.
     * @param bool|null $omnium A boolean flag to optionally filter declarations by omnium. Defaults to null.
     *
     * @return Collection<int,Declaration> A collection of filtered declarations matching the provided criteria.
     */
    public static function findByYear(int $year, array $departments = [], ?bool $omnium = null): Collection
    {
       return Declaration::query()
            ->with('trips')
            ->whereYear('created_at', $year)
            ->when($departments, function ($query, $departments) {
                $query->where(function ($q) use ($departments) {
                    foreach ($departments as $department) {
                        // Handle both plain text and JSON array formats
                        $q->orWhere('departments', $department)
                            ->orWhereJsonContains('departments', $department);
                    }
                });
            })
            ->when($omnium !== null, fn($query) => $query->where('omnium', $omnium))
            ->get();
    }
}
