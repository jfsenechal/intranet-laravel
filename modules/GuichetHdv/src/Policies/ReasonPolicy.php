<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Policies;

use AcMarche\GuichetHdv\Enums\RolesEnum;
use AcMarche\GuichetHdv\Models\Reason;
use App\Models\User;

final class ReasonPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasAdminRole($user);
    }

    public function view(User $user, Reason $reason): bool
    {
        return $this->hasAdminRole($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAdminRole($user);
    }

    public function update(User $user, Reason $reason): bool
    {
        return $this->hasAdminRole($user);
    }

    public function delete(User $user, Reason $reason): bool
    {
        return $this->hasAdminRole($user);
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    private function hasAdminRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([RolesEnum::ROLE_GUICHET_AGENT->value]);
    }
}
