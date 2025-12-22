<?php

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\Declaration;
use Illuminate\Database\Eloquent\Builder;

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
}
