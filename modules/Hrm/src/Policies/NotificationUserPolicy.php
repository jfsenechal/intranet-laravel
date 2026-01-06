<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\NotificationUser;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class NotificationUserPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, NotificationUser $notificationUser): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, NotificationUser $notificationUser): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, NotificationUser $notificationUser): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, NotificationUser $notificationUser): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, NotificationUser $notificationUser): bool
    {
        return false;
    }
}
