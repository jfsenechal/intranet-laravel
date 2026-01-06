<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\Sms;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class SmsPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, Sms $sms): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, Sms $sms): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, Sms $sms): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Sms $sms): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Sms $sms): bool
    {
        return false;
    }
}
