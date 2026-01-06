<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\ContractType;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class ContractTypePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, ContractType $contractType): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, ContractType $contractType): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, ContractType $contractType): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, ContractType $contractType): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, ContractType $contractType): bool
    {
        return false;
    }
}
