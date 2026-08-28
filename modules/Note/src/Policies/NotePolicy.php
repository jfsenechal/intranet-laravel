<?php

declare(strict_types=1);

namespace AcMarche\Note\Policies;

use AcMarche\Note\Models\Note;
use App\Models\User;

/**
 * Notes are strictly private: a user only ever sees, edits and deletes the notes
 * they authored. Ownership is carried by the `user_add` column, which
 * AcMarche\Security\Models\HasUserAdd fills with the author's username on create.
 *
 * Administrators are deliberately NOT exempt here.
 */
final class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * The list page itself is open; NoteResource::getEloquentQuery() narrows it
     * to the current user's own notes.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        return $this->owns($user, $note);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        return $user instanceof User;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        return $this->owns($user, $note);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $this->owns($user, $note);
    }

    /**
     * Determine whether the user can bulk delete models.
     *
     * Safe to allow outright: the table query is scoped to the current user, so a
     * selection can never contain a note belonging to somebody else.
     */
    public function deleteAny(): bool
    {
        return true;
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

    public function owns(User $user, Note $note): bool
    {
        return $user->username === $note->user_add;
    }
}
