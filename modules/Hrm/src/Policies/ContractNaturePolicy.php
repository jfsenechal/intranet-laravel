<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\ContractNature;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class ContractNaturePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, ContractNature $contractNature): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, ContractNature $contractNature): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, ContractNature $contractNature): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, ContractNature $contractNature): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, ContractNature $contractNature): bool
    {
        return false;
    }
}
