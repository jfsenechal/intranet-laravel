<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Policies;

use AcMarche\Offenses\Enums\RolesEnum;
use App\Models\User;

trait PolicyTrait
{
    public function hasRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_OFFENSE->value, RolesEnum::ROLE_OFFENSE_ADMIN->value])) {
            return true;
        }

        return false;
    }

    public function hasRoleAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_OFFENSE_ADMIN->value])) {
            return true;
        }

        return false;
    }
}
