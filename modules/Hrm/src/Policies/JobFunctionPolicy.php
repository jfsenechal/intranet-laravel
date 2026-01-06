<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\JobFunction;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class JobFunctionPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, JobFunction $jobFunction): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, JobFunction $jobFunction): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, JobFunction $jobFunction): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, JobFunction $jobFunction): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, JobFunction $jobFunction): bool
    {
        return false;
    }
}
