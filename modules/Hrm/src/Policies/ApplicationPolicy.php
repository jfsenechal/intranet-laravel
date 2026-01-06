<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Application;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class ApplicationPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Application $application): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Application $application): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Application $application): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Application $application): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Application $application): bool
    {
        return false;
    }
}
