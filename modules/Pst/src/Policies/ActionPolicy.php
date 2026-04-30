<?php

declare(strict_types=1);

namespace AcMarche\Pst\Policies;

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Models\Action;
use App\Models\User;

// https://laravel.com/docs/12.x/authorization#creating-policies
final class ActionPolicy
{
    use ActionEditPolicyTrait;

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
        return ! $user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Action $action): bool
    {
        return self::isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Action $action): bool
    {
        return self::isUserLinkedToAction($user, $action);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user): bool
    {
        return $user->hasOneOfThisRoles([RoleEnum::ADMIN->value]);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }
}
