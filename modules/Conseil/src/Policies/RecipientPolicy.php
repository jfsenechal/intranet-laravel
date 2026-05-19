<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Policies;

use AcMarche\Conseil\Enums\RolesEnum;
use App\Models\User;

final class RecipientPolicy
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

    public function update(User $user): bool
    {
        return $this->hasRole($user);
    }

    public function delete(User $user): bool
    {
        return $this->hasRole($user);
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    public function hasRole(User $user): bool
    {
        if ($user->isAdministrator()) {
            return true;
        }

        if ($user->hasOneOfThisRoles([RolesEnum::ROLE_CONSEIL_ADMIN->value])) {
            return true;
        }

        return false;
    }
}
