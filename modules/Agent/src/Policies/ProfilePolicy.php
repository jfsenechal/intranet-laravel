<?php

declare(strict_types=1);

namespace AcMarche\Agent\Policies;

use AcMarche\Agent\Models\Profile;
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

    /**
     * Administrators may always edit. Anybody the profile has been delegated to
     * may edit that profile only, so they can complete it.
     */
    public function update(User $user, ?Profile $profile = null): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $profile instanceof Profile && $this->isDelegate($user, $profile);
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

    /**
     * The profile has been shared with the user, who still holds the agent role.
     */
    private function isDelegate(User $user, Profile $profile): bool
    {
        if (blank($user->email) || ! $this->hasAgentAccess($user)) {
            return false;
        }

        return $profile->shares()->where('shared_for', $user->email)->exists();
    }
}
