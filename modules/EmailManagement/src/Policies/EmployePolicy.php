<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Policies;

use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Policies\Concerns\EmailManagementAuthorization;
use App\Models\User;

final class EmployePolicy
{
    use EmailManagementAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function view(User $user, Employe $employe): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function update(User $user, Employe $employe): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function delete(User $user, Employe $employe): bool
    {
        return $this->isEmailAdmin($user);
    }

    public function restore(User $user, Employe $employe): bool
    {
        return false;
    }

    public function forceDelete(User $user, Employe $employe): bool
    {
        return false;
    }
}
