<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\HrNotification;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class HrNotificationPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, HrNotification $hrNotification): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, HrNotification $hrNotification): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, HrNotification $hrNotification): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, HrNotification $hrNotification): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, HrNotification $hrNotification): bool
    {
        return false;
    }
}
