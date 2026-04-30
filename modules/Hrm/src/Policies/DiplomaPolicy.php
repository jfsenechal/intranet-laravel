<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class DiplomaPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Diploma $diploma): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $diploma->employee !== null
            && $this->canViewEmployee($user, $diploma->employee);
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
