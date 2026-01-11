<?php

namespace AcMarche\Courrier\Policies;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;

final class RecipientPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $this->isAdministrator($user);
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Recipient $recipient): bool
    {
        return $this->isAdministrator($user);
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
    public function update(User $user, Recipient $recipient): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recipient $recipient): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Recipient $recipient): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Recipient $recipient): bool
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
