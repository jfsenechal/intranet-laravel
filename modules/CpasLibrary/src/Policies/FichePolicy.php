<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Policies;

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Models\Fiche;
use App\Models\User;

final class FichePolicy
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

    public function update(User $user, Fiche $fiche): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->hasRole($user) && $fiche->userAdd === $user->username;
    }

    public function delete(User $user, Fiche $fiche): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->hasRole($user) && $fiche->userAdd === $user->username;
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

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_LIBRARY_ADMIN->value,
            RolesEnum::ROLE_LIBRARY->value,
        ]);
    }

    private function isAdmin(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([RolesEnum::ROLE_LIBRARY_ADMIN->value]);
    }
}
