<?php

declare(strict_types=1);

namespace AcMarche\KnowledgeBase\Tests;

use AcMarche\KnowledgeBase\Enums\RolesEnum;
use AcMarche\Security\Enums\RolesEnum as SecurityRolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;

/**
 * Builds the users of the knowledge base access matrix.
 */
final class KnowledgeBaseUsers
{
    public static function make(string $profile): ?User
    {
        return match ($profile) {
            'guest' => null,
            'no role' => User::factory()->create(),
            'reader' => self::withRole(RolesEnum::ROLE_KNOWLEDGE_BASE->value),
            'editor' => self::withRole(RolesEnum::ROLE_KNOWLEDGE_BASE_ADMIN->value),
            'administrator' => User::factory()->create(['is_administrator' => true]),
            'intranet admin role' => self::withRole(SecurityRolesEnum::INTRANET_ADMIN->value),
        };
    }

    private static function withRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['name' => $roleName]));

        return $user;
    }
}
