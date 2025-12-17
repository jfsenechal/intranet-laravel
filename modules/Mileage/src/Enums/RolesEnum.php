<?php

namespace AcMarche\Mileage\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_FINANCE_DEPLACEMENT_ADMIN = 'ROLE_FINANCE_DEPLACEMENT_ADMIN';
    case ROLE_FINANCE_DEPLACEMENT_VILLE = 'ROLE_FINANCE_DEPLACEMENT_VILLE';
    case ROLE_FINANCE_DEPLACEMENT_CPAS = 'ROLE_FINANCE_DEPLACEMENT_CPAS';

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
            self::ROLE_FINANCE_DEPLACEMENT_ADMIN => 'Admin deplacements',
            self::ROLE_FINANCE_DEPLACEMENT_VILLE => 'Access deplacements ville',
            self::ROLE_FINANCE_DEPLACEMENT_CPAS => 'Access deplacements CPAS',
            default => null
        };
    }
}
