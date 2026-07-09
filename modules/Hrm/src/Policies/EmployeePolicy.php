<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class EmployeePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    /**
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->scopeVisibleEmployees($query, $user);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->canViewEmployee($user, $employee);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(): bool
    {
        return false;
    }
}
