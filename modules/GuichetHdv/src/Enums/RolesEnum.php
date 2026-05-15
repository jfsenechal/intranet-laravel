<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_GUICHET_AGENT = 'ROLE_GUICHET_AGENT';
    case ROLE_GUICHET = 'ROLE_GUICHET';

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
            self::ROLE_GUICHET_AGENT => 'Admin guichet',
            self::ROLE_GUICHET => 'Agent guichet',
        };
    }
}
