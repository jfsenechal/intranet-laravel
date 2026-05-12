<?php

declare(strict_types=1);

namespace AcMarche\Agent\Policies\Concerns;

use AcMarche\Agent\Enums\RolesEnum;
use App\Models\User;

trait AgentAuthorization
{
    protected function isAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_AGENT_ADMIN->value,
        ]);
    }

    private function hasAgentAccess(User $user): bool
    {
        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_AGENT->value,
            RolesEnum::ROLE_AGENT_ADMIN->value,
        ]);
    }
}
