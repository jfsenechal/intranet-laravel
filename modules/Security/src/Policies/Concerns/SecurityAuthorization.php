<?php

declare(strict_types=1);

namespace AcMarche\Security\Policies\Concerns;

use AcMarche\Security\Enums\RolesEnum;
use App\Models\User;

trait SecurityAuthorization
{
    protected function isIntranetAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::INTRANET_ADMIN->value);
    }
}
