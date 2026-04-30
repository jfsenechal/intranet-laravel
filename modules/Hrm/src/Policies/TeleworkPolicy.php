<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class TeleworkPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Telework $telework): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $telework->user_add === $user->username;
    }

    public function create(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
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
