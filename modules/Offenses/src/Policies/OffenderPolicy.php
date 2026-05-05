<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Policies;

use AcMarche\Offenses\Models\Offender;
use App\Models\User;

final class OffenderPolicy
{
    use PolicyTrait;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Offender $offender): bool
    {
        return $this->hasRole($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasRoleAdmin($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Offender $offender): bool
    {
        return $this->hasRoleAdmin($user, $offender);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Offender $offender): bool
    {
        return $this->hasRoleAdmin($user, $offender);
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
}
