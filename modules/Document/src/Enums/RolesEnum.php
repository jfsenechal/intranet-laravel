<?php

declare(strict_types=1);

namespace AcMarche\Document\Enums;

enum RolesEnum: string
{
    case ROLE_DOCUMENT_ADMIN = 'ROLE_DOCUMENT_ADMIN';

    /**
     * @return array<string, string>
     */
    public static function getRoles(): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            $roles[$role->value] = $role->value;
        }

        return $roles;
    }
}
