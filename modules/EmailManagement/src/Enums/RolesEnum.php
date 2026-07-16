<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_EMAIL_ADMIN = 'ROLE_EMAIL_ADMIN';

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

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ROLE_EMAIL_ADMIN => 'Accès administrateur à la gestion des emails',
        };
    }
}
