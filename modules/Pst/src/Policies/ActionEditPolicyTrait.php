<?php

declare(strict_types=1);

namespace AcMarche\Pst\Policies;

use AcMarche\Pst\Enums\RoleEnum;

trait ActionEditPolicyTrait
{
    public static function isUserLinkedToAction($user, $action): bool
    {
        if ($user->hasOneOfThisRoles([RoleEnum::MANDATAIRE->value])) {
            return false;
        }
        if ($user->hasOneOfThisRoles([RoleEnum::ADMIN->value])) {
            return true;
        }

        // Check if user is directly linked to the action
        if ($action->users()->where('action_user.username', $user->username)->exists()) {
            return true;
        }

        return $action->leaderServices()
            ->whereHas('users', function ($query) use ($user): void {
                $query->where('service_user.username', $user->username);
            })
            ->exists();
    }
}
