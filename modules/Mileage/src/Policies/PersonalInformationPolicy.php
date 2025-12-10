<?php

namespace AcMarche\Mileage\Policies;

// https://laravel.com/docs/12.x/authorization#creating-policies
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\PersonalInformation;
use App\Models\User;

final class PersonalInformationPolicy
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
    public function view(User $user, PersonalInformation $personalInformation): bool
    {
        return $this->canWrite($user, $personalInformation);
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
    public function update(User $user, PersonalInformation $personalInformation): bool
    {
        return $this->canWrite($user, $personalInformation);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PersonalInformation $personalInformation): bool
    {
        return $this->canWrite($user, $personalInformation);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PersonalInformation $personalInformation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PersonalInformation $personalInformation): bool
    {
        return false;
    }

    /**
     * Check if user is linked to the action either directly or through services
     */
    private function canWrite(User $user, PersonalInformation $personalInformation): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return $personalInformation->username === $user->username;
    }
}
