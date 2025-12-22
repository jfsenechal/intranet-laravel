<?php

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\Declaration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
     * Retrieve all declarations for a specific user.
     *
     * @return Collection<int, Declaration>
     */
    public static function findByUsername(string $username): Collection
    {
        return Declaration::query()
            ->with('trips')
            ->where('user_add', $username)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get one declaration for a user (for profile info fallback).
     */
    public static function getOneDeclarationByUsername(string $username): ?Declaration
    {
        return Declaration::query()
            ->where('user_add', $username)
            ->first();
    }

    /**
     * Get all distinct usernames that have declarations.
     *
     * @return array<string, string>
     */
    public static function getAllUsernames(): array
    {
        return Declaration::query()
            ->distinct()
            ->orderBy('user_add')
            ->pluck('user_add', 'user_add')
            ->toArray();
    }

    /**
     * Get kilometers by year and month for a user with a specific movement type.
     *
     * @return array<int, array<int, int>>
     */
    public static function getKilometersByYearMonth(string $username, string $typeMovement): array
    {
        $declarations = Declaration::query()
            ->with('trips')
            ->where('user_add', $username)
            ->where('type_movement', $typeMovement)
            ->get();

        $result = [];
        foreach ($declarations as $declaration) {
            foreach ($declaration->trips as $trip) {
                $year = $trip->departure_date->year;
                $month = $trip->departure_date->month;

                if (! isset($result[$year])) {
                    $result[$year] = [];
                }
                if (! isset($result[$year][$month])) {
                    $result[$year][$month] = 0;
                }
                $result[$year][$month] += $trip->distance;
            }
        }

        return $result;
    }

    /**
     * Retrieve a collection of declarations filtered by the specified year, departments, and omnium flag.
     *
     * @param  int  $year  The year to filter the declarations by.
     * @param  array  $departments  An array of department identifiers to filter the declarations. Defaults to an empty array.
     * @param  bool|null  $omnium  A boolean flag to optionally filter declarations by omnium. Defaults to null.
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
            ->when($omnium !== null, fn ($query) => $query->where('omnium', $omnium))
            ->get();
    }
}
