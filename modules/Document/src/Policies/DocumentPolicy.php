<?php

declare(strict_types=1);

namespace AcMarche\Document\Policies;

use AcMarche\Document\Enums\RolesEnum;
use AcMarche\Document\Models\Document;
use App\Models\User;

final class DocumentPolicy
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
    public function create(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Document $document): bool
    {
        return $this->isAdministrator($user, $document);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->isAdministrator($user, $document);
    }

    /**
     * Determine whether the user can delete models in bulk.
     *
     * Without this method Filament would allow the bulk delete action to
     * everyone. It cannot check `user_add` per record, so unlike `delete()` it
     * is limited to administrators and the document admin role.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([RolesEnum::ROLE_DOCUMENT_ADMIN->value]);
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

    private function isAdministrator(User $user, Document $document): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }
        if ($user->hasOneOfThisRoles(
            [
                RolesEnum::ROLE_DOCUMENT_ADMIN->value,
            ]
        )) {
            return true;
        }

        return $user->username === $document->user_add;
    }
}
