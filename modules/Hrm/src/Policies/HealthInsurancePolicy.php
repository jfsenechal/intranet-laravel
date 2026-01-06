<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\HealthInsurance;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class HealthInsurancePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, HealthInsurance $healthInsurance): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, HealthInsurance $healthInsurance): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, HealthInsurance $healthInsurance): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, HealthInsurance $healthInsurance): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, HealthInsurance $healthInsurance): bool
    {
        return false;
    }
}
