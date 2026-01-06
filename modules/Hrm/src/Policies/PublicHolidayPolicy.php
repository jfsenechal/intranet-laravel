<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\PublicHoliday;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class PublicHolidayPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, PublicHoliday $publicHoliday): bool
    {
        return $this->hasReadAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, PublicHoliday $publicHoliday): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, PublicHoliday $publicHoliday): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, PublicHoliday $publicHoliday): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, PublicHoliday $publicHoliday): bool
    {
        return false;
    }
}
