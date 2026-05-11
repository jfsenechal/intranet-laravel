<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Policies\Concerns;

use AcMarche\Mediation\Enums\RolesEnum;
use App\Models\User;

trait MediationAuthorization
{
    public function hasRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_MEDIATION_ADMIN->value])) {
            return true;
        }

        return false;
    }
}
