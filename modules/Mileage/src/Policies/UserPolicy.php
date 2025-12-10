<?php

namespace AcMarche\Mileage\Policies;

// https://laravel.com/docs/12.x/authorization#creating-policies
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use App\Models\User;

final class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->canWrite($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BudgetArticle $budgetArticle): bool
    {

        return $this->canWrite($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {

        return $this->canWrite($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BudgetArticle $budgetArticle): bool
    {

        return $this->canWrite($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BudgetArticle $budgetArticle): bool
    {
        return $this->canWrite($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BudgetArticle $budgetArticle): bool
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

    private function canWrite(User $user): bool
    {
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return true;
        }

        return false;
    }
}
