<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Policies;

use AcMarche\StreetWatch\Enums\RolesEnum;
use AcMarche\StreetWatch\Models\Incident;
use App\Models\User;

final class IncidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasRole($user);
    }

    public function view(User $user): bool
    {
        return $this->hasRole($user);
    }

    public function create(User $user): bool
    {
        return $this->hasRole($user);
    }

    public function update(User $user, Incident $incident): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->hasRole($user) && $incident->user_add === $user->username;
    }

    public function delete(User $user, Incident $incident): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->hasRole($user) && $incident->user_add === $user->username;
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    private function hasRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([RolesEnum::ROLE_STREET_WATCH->value]);
    }

    private function isAdmin(User $user): bool
    {
        return $user->isAdministrator();
    }
}
