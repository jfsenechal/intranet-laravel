<?php

namespace AcMarche\Mileage\Policies;

// https://laravel.com/docs/12.x/authorization#creating-policies
use AcMarche\Mileage\Models\BudgetArticle;
use App\Models\User;

final class BudgetArticlePolicy
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
    public function view(User $user, BudgetArticle $budgetArticle): bool
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

        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BudgetArticle $budgetArticle): bool
    {
        return $this->isUserLinkedToAction($user, $budgetArticle);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(BudgetArticle $user, BudgetArticle $budgetArticle): bool
    {
        return $this->isUserLinkedToAction($user, $budgetArticle);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(BudgetArticle $user, BudgetArticle $budgetArticle): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(BudgetArticle $user, BudgetArticle $budgetArticle): bool
    {
        return false;
    }

    /**
     * Check if user is linked to the action either directly or through services
     */
    private function isUserLinkedToAction(User $user, BudgetArticle $budgetArticle): bool
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
