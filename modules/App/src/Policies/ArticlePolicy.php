<?php

declare(strict_types=1);

namespace AcMarche\App\Policies;

use AcMarche\Security\Enums\RolesEnum;
use App\Models\User;

final class ArticlePolicy
{
    /**
     * Articles are readable by every authenticated user.
     */
    public function viewAny(): bool
    {
        return true;
    }

    public function view(): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function update(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function delete(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isIntranetAdmin($user);
    }

    public function restore(): bool
    {
        return false;
    }

    public function forceDelete(): bool
    {
        return false;
    }

    /**
     * Deliberately not using the SecurityAuthorization concern: writing articles is
     * reserved to ROLE_INTRANET_ADMIN alone, the `is_administrator` flag grants nothing.
     */
    private function isIntranetAdmin(User $user): bool
    {
        return $user->hasRole(RolesEnum::INTRANET_ADMIN->value);
    }
}
