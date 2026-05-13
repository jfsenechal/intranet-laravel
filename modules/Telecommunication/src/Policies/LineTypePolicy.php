<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Policies;

use AcMarche\Telecommunication\Enums\RolesEnum;
use App\Models\User;

final class LineTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->hasRole($user);
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

    public function hasRole(User $user)
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_TELEPHONE->value])) {
            return true;
        }

        return false;
    }
}
