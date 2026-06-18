<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Policies;

use AcMarche\EmailManagement\Models\Employe;
use App\Models\User;

final class EmployePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employe|array $citoyen): bool
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employe|array $citoyen): bool
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employe|array $citoyen): bool
    {
        return auth()->check();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Employe|array $citoyen): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Employe|array $citoyen): bool
    {
        return false;
    }
}
