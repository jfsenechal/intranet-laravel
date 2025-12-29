<?php

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\PersonalInformation;
use Illuminate\Database\Eloquent\Builder;

final class PersonalInformationRepository
{
    public static function getByCurrentUser(): Builder
    {
        return self::modifyQueryToGetByCurrentUser(PersonalInformation::query());
    }

    public static function modifyQueryToGetByCurrentUser(Builder $builder): Builder
    {
        return $builder->where('username', auth()->user()?->username);
    }
}
