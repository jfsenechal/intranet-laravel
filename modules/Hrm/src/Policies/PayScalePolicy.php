<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\PayScale;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class PayScalePolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, PayScale $payScale): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, PayScale $payScale): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, PayScale $payScale): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, PayScale $payScale): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, PayScale $payScale): bool
    {
        return false;
    }
}
