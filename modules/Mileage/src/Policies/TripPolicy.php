<?php

namespace AcMarche\Mileage\Policies;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;

final class TripPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasOneOfThisRoles(RolesEnum::getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Trip $trip): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return $trip->username === $user->username;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles(RolesEnum::getRoles())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Trip $trip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Trip $trip): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        if ($trip->isDeclared()) {
            return false;
        }

        return $trip->username === $user->username;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Trip $trip): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Trip $trip): bool
    {
        return false;
    }
}
