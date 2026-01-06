<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class DirectionPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Direction $direction): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Direction $direction): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Direction $direction): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Direction $direction): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Direction $direction): bool
    {
        return false;
    }
}
