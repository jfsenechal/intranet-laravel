<?php

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\HrDocument;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class HrDocumentPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->hasAnyHrmRole($user);
    }

    public function view(User $user, HrDocument $hrDocument): bool
    {
        return $this->hasReadAccess($user) || $this->isDirectionHead($user);
    }

    public function create(User $user): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function update(User $user, HrDocument $hrDocument): bool
    {
        return $this->hasWriteAccess($user);
    }

    public function delete(User $user, HrDocument $hrDocument): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, HrDocument $hrDocument): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, HrDocument $hrDocument): bool
    {
        return false;
    }
}
