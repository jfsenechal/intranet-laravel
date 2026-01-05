<?php

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Service;
use App\Models\User;

final class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        return false;
    }

    private function isAdministrator(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasOneOfThisRoles(
            [
                RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN,
                RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN,
                RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN,
            ]
        )) {
            return true;
        }

        return false;
    }
}
