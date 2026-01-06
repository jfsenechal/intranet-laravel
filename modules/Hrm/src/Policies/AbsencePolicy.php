<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class AbsencePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Absence $absence): bool
    {
        return $this->hasReadAccess($user) || $this->isDirectionHead($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Absence $absence): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Absence $absence): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Absence $absence): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Absence $absence): bool
    {
        return false;
    }
}
