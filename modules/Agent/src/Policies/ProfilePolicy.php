<?php

declare(strict_types=1);

namespace AcMarche\Agent\Policies;

use AcMarche\Agent\Policies\Concerns\AgentAuthorization;
use App\Models\User;

final class ProfilePolicy
{
    use AgentAuthorization;

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
        return $this->isAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
