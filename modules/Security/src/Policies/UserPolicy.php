<?php

declare(strict_types=1);

namespace AcMarche\Security\Policies;

use AcMarche\Security\Policies\Concerns\SecurityAuthorization;
use App\Models\User;

final class UserPolicy
{
    use SecurityAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isIntranetAdmin($user) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isIntranetAdmin($user) || $user->id === $model->id;
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
