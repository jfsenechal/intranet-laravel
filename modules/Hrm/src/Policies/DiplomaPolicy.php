<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class DiplomaPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Diploma $diploma): bool
    {
        return $this->hasReadAccess($user) || $this->isDirectionHead($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Diploma $diploma): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Diploma $diploma): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Diploma $diploma): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Diploma $diploma): bool
    {
        return false;
    }
}
