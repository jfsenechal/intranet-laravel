<?php

declare(strict_types=1);

namespace AcMarche\Document\Policies;

use AcMarche\Document\Enums\RolesEnum;
use App\Models\User;

final class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * The nullable parameter is what lets a guest through: `Gate` only calls a
     * policy method without a user when its first parameter accepts null.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user): bool
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
    public function update(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Determine whether the user can delete models in bulk.
     *
     * Without this method Filament would allow the bulk delete action to
     * everyone.
     */
    public function deleteAny(User $user): bool
    {
        return $this->isAdministrator($user);
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

    private function isAdministrator(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles(
            [
                RolesEnum::ROLE_DOCUMENT_ADMIN->value,
            ]
        );
    }
}
