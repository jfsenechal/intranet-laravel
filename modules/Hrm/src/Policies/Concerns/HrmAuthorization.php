<?php

namespace AcMarche\Hrm\Policies\Concerns;

use AcMarche\Mileage\Enums\RolesEnum;
use App\Models\User;

trait HrmAuthorization
{
    protected function isAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value);
    }

    protected function canManageCpas(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_CPAS->value);
    }

    protected function canManageVille(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_VILLE->value);
    }

    protected function canReadCpas(User $user): bool
    {
        if ($this->canManageCpas($user)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_CPAS_READ->value);
    }

    protected function canReadVille(User $user): bool
    {
        if ($this->canManageVille($user)) {
            return true;
        }

        return $user->hasRole(RolesEnum::ROLE_GRH_VILLE_READ->value);
    }

    protected function isDirectionHead(User $user): bool
    {
        return $user->hasRole(RolesEnum::ROLE_GRH_DIRECTION->value);
    }

    protected function hasAnyHrmRole(User $user): bool
    {
        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_GRH->value,
            RolesEnum::ROLE_GRH_ADMIN->value,
            RolesEnum::ROLE_GRH_CPAS->value,
            RolesEnum::ROLE_GRH_VILLE->value,
            RolesEnum::ROLE_GRH_CPAS_READ->value,
            RolesEnum::ROLE_GRH_VILLE_READ->value,
            RolesEnum::ROLE_GRH_DIRECTION->value,
        ]);
    }

    protected function hasReadAccess(User $user): bool
    {
        return $this->canReadCpas($user) || $this->canReadVille($user);
    }

    protected function hasWriteAccess(User $user): bool
    {
        return $this->canManageCpas($user) || $this->canManageVille($user);
    }
}
