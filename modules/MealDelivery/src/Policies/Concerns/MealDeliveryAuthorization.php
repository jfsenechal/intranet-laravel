<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Policies\Concerns;

use AcMarche\MealDelivery\Enums\RolesEnum;
use App\Models\User;

trait MealDeliveryAuthorization
{
    protected function isAdmin(User $user): bool
    {
        return $user->isAdministrator();
    }

    protected function canAccess(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_CPAS_REPAS->value);
    }
}
