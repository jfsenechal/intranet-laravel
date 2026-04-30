<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_GRH_ADMIN = 'ROLE_GRH_ADMIN';
    case ROLE_GRH_CPAS_READ = 'ROLE_GRH_CPAS_READ';
    case ROLE_GRH_VILLE_READ = 'ROLE_GRH_VILLE_READ';
    case ROLE_GRH_DIRECTION = 'ROLE_GRH_DIRECTION';

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
            self::ROLE_GRH_ADMIN => 'Access administrateur Ville et CPAS',
            self::ROLE_GRH_CPAS_READ => 'Accès à tous les employés du Cpas en lecture',
            self::ROLE_GRH_VILLE_READ => 'Accès à tous les employés de la Ville en lecture',
            self::ROLE_GRH_DIRECTION => 'Accès pour directeurs à leurs employés',
        };
    }
}
