<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class EmployeePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->hasReadAccess($user) || $this->isDirectionHead($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }
}
