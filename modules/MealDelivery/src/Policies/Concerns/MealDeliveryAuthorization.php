<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Policies\Concerns;

use AcMarche\MealDelivery\Enums\RolesEnum;
use App\Models\User;

trait MealDeliveryAuthorization
{
    protected static function canAccessStatic(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_CPAS_REPAS->value);
    }

    protected function isAdmin(User $user): bool
    {
        return $user->isAdministrator();
    }

    protected function canAccess(User $user): bool
    {
        return self::canAccessStatic($user);
    }
}
