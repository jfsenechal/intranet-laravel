<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Policies;

use AcMarche\Hrm\Models\HrDocument;
use AcMarche\Hrm\Policies\Concerns\HrmAuthorization;
use App\Models\User;

final class HrDocumentPolicy
{
    use HrmAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, HrDocument $document): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $document->employee !== null
            && $this->canViewEmployee($user, $document->employee);
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
