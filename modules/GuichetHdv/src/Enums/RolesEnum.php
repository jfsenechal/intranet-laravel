<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_EGUICHET_ADMIN = 'ROLE_EGUICHET_ADMIN';
    case ROLE_EGUICHET = 'ROLE_EGUICHET';

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
            self::ROLE_EGUICHET_ADMIN => 'Admin guichet',
            self::ROLE_EGUICHET => 'Agent guichet',
        };
    }
}
