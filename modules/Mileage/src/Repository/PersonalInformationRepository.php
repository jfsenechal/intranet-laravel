<?php

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\PersonalInformation;
use Illuminate\Database\Eloquent\Builder;

class PersonalInformationRepository
{
    public static function getByCurrentUser(): Builder
    {
        return PersonalInformation::query()->where('username', auth()->user()?->username);
    }
}
