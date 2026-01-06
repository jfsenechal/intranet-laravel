<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Prerequisite;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class PrerequisitePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Prerequisite $prerequisite): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Prerequisite $prerequisite): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Prerequisite $prerequisite): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Prerequisite $prerequisite): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Prerequisite $prerequisite): bool
    {
        return false;
    }
}
