<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_OFFENSE_ADMIN = 'ROLE_SANCTION_ADMIN';
    case ROLE_OFFENSE = 'ROLE_SANCTION';

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
            self::ROLE_OFFENSE_ADMIN => 'Sanction admin',
            self::ROLE_OFFENSE => 'Sanction lecture',
            default => null
        };
    }
}
