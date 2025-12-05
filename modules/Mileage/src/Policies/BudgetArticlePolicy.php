<?php

namespace AcMarche\News\Policies;

// https://laravel.com/docs/12.x/authorization#creating-policies
use AcMarche\Mileage\Models\BudgetArticle;

final class BudgetArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(BudgetArticle $budgetArticle): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(BudgetArticle $budgetArticle): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(BudgetArticle $budgetArticle): bool
    {
        return true;
        if ($user->hasRoles([RoleEnum::MANDATAIRE->value])) {

        }

        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(BudgetArticle $budgetArticle): bool
    {
        return $this->isUserLinkedToAction($budgetArticle);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(BudgetArticle $budgetArticle): bool
    {
        return $this->isUserLinkedToAction($budgetArticle);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(BudgetArticle $budgetArticle): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(BudgetArticle $budgetArticle): bool
    {
        return false;
    }

    /**
     * Check if user is linked to the action either directly or through services
     */
    private function isUserLinkedToAction(BudgetArticle $budgetArticle): bool
    {
        return true;
        if ($user->hasRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }
        if ($user->hasRoles([RoleEnum::ADMIN->value])) {
            return true;
        }
        // Check if user is directly linked to the action
        $directlyLinked = $action->users()->where('user_id', $budgetArticle->id)->exists();

        if ($directlyLinked) {
            return true;
        }

        // Check if user is member of any service that is linked to the action
        return $action->leaderServices()
            ->whereHas('users', function ($query) use ($budgetArticle) {
                $query->where('user_id', $budgetArticle->id);
            })
            ->exists();
    }
}
