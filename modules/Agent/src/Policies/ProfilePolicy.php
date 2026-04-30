<?php

declare(strict_types=1);

namespace AcMarche\Agent\Policies;

use AcMarche\Agent\Enums\RolesEnum;
use App\Models\User;

final class ProfilePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasAgentAccess($user);
    }

    public function view(User $user): bool
    {
        return $this->hasAgentAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAgentAdministrator($user);
    }

    public function update(User $user): bool
    {
        return $this->isAgentAdministrator($user);
    }

    public function delete(User $user): bool
    {
        return $this->isAgentAdministrator($user);
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    private function hasAgentAccess(User $user): bool
    {
        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_AGENT->value,
            RolesEnum::ROLE_AGENT_ADMIN->value,
        ]);
    }

    private function isAgentAdministrator(User $user): bool
    {
        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_AGENT_ADMIN->value,
        ]);
    }
}
