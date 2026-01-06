<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Operator;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class OperatorPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Operator $operator): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Operator $operator): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Operator $operator): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Operator $operator): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Operator $operator): bool
    {
        return false;
    }
}
