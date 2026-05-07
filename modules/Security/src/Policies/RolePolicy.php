<?php

declare(strict_types=1);

namespace AcMarche\Security\Policies;

use AcMarche\Security\Policies\Concerns\SecurityAuthorization;
use App\Models\User;

final class RolePolicy
{
    use SecurityAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function view(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function restore(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
