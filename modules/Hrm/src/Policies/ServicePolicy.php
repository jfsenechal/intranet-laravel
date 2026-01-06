<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Service;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class ServicePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return false;
    }
}
