<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase;

use AcMarche\KnowledgeBase\Enums\RolesEnum;
use AcMarche\Security\Enums\RolesEnum as SecurityRolesEnum;
use App\Models\User;

/**
 * Single authorization rule of the knowledge base.
 *
 * User::canAccessPanel() lets everybody into every panel, so each page and the
 * image route call these checks themselves. Access is limited to the two module
 * roles and to intranet administrators (same check as User::canImpersonate()).
 */
final class KnowledgeBaseAccess
{
    public static function canRead(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return self::isIntranetAdmin($user) || $user->hasOneOfThisRoles(RolesEnum::getRoles());
    }

    public static function canWrite(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return self::isIntranetAdmin($user) || $user->hasRole(RolesEnum::ROLE_KNOWLEDGE_BASE_ADMIN->value);
    }

    private static function isIntranetAdmin(User $user): bool
    {
        return $user->isAdministrator() || $user->hasRole(SecurityRolesEnum::INTRANET_ADMIN->value);
    }
}
