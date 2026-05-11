<?php

declare(strict_types=1);

namespace AcMarche\Publication\Policies;

use AcMarche\Publication\Enums\RolesEnum;
use AcMarche\Publication\Models\Publication;
use App\Models\User;

final class PublicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Publication $publication): bool
    {
        return $this->hasRole($user, $publication);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Publication $publication): bool
    {
        return $this->hasRole($user, $publication);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }

    public function hasRole(User $user, Publication $publication)
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_PUBLICATION->value])) {
            return true;
        }       return false;
    }
}
