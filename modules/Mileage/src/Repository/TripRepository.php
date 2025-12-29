<?php

namespace AcMarche\Mileage\Repository;

use Illuminate\Database\Eloquent\Builder;

final class TripRepository
{
    public static function getByUser(Builder $query): Builder
    {
        $user = auth()->user();
        $username = $user->username;
        // todo remove
        $username = 'aaguirre';

        return $query->where('user_add', '=', $username);
    }
}
