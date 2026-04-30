<?php

declare(strict_types=1);

namespace AcMarche\Ad\Policies;

use AcMarche\Ad\Enums\RolesEnum;
use AcMarche\Ad\Models\ClassifiedAd;
use App\Models\User;

final class ClassifiedAdPolicy
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
    public function update(User $user, ClassifiedAd $classifiedAd): bool
    {
        return $this->hasRole($user, $classifiedAd);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassifiedAd $classifiedAd): bool
    {
        return $this->hasRole($user, $classifiedAd);
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

    public function hasRole(User $user, ClassifiedAd $classifiedAd)
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_AD_ADMIN->value])) {
            return true;
        }

        return $user->username === $classifiedAd->user_add;
    }
}
