<?php

namespace AcMarche\Mileage\Policies;

// https://laravel.com/docs/12.x/authorization#creating-policies
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\Rate;
use App\Models\User;

final class RatePolicy
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
    public function view(User $user, Rate $rate): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Rate $rate): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rate $rate): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Rate $user, Rate $rate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Rate $user, Rate $rate): bool
    {
        return false;
    }
}
