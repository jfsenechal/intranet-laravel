<?php

declare(strict_types=1);

namespace AcMarche\Pst\Policies;

use AcMarche\Pst\Enums\RoleEnum;
use App\Models\User;

final class StrategicObjectivePolicy
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
    public function create(User $user): bool
    {
        return $this->hasRoles($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasRoles($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->hasRoles($user);
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

    private function hasRoles(User $user): bool
    {
        if ($user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }

        return $user->hasOneOfThisRoles([RoleEnum::ADMIN->value]);
    }
}
