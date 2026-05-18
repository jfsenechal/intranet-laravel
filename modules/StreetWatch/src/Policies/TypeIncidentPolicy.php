<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Policies;

use AcMarche\StreetWatch\Enums\RolesEnum;
use App\Models\User;

final class TypeIncidentPolicy
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
