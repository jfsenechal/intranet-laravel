<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class DeadlinePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Deadline $deadline): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Deadline $deadline): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Deadline $deadline): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Deadline $deadline): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Deadline $deadline): bool
    {
        return false;
    }
}
