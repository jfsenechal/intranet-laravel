<?php

namespace AcMarche\News\Policies;

use AcMarche\News\Models\Category;
use App\Models\User;

// https://laravel.com/docs/12.x/authorization#creating-policies
final class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $action): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
        if ($user->hasRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $action): bool
    {
        return $this->isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $action): bool
    {
        return $this->isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $action): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $action): bool
    {
        return false;
    }

    /**
     * Check if user is linked to the action either directly or through services
     */
    private function isUserLinkedToAction(User $user, Category $action): bool
    {
        return true;
        if ($user->hasRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }
        if ($user->hasRoles([RoleEnum::ADMIN->value])) {
            return true;
        }
        // Check if user is directly linked to the action
        $directlyLinked = $action->users()->where('user_id', $user->id)->exists();

        if ($directlyLinked) {
            return true;
        }

        // Check if user is member of any service that is linked to the action
        return $action->leaderServices()
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }
}
