<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use AcMarche\App\Enums\DepartmentEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RolesEnum: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case ADMIN = 'ROLE_PST_ADMIN';
    case MANDATAIRE = 'ROLE_PST_MANDATAIRE';
    case VILLE = 'ROLE_PST_VILLE';
    case CPAS = 'ROLE_PST_CPAS';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    /**
     * The role giving access to the PST of the given department.
     */
    public static function forDepartment(DepartmentEnum $department): self
    {
        return match ($department) {
            DepartmentEnum::VILLE => self::VILLE,
            DepartmentEnum::CPAS => self::CPAS,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur PST',
            self::MANDATAIRE => 'Mandataire',
            self::VILLE => 'PST Ville',
            self::CPAS => 'PST Cpas',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => 'success',
            self::MANDATAIRE => 'primary',
            self::VILLE => 'danger',
            self::CPAS => 'warning',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ADMIN => 'Gestion des actions,des agents et des paramètres',
            self::MANDATAIRE => 'Accès en lecture seul',
            self::VILLE => 'Accès au PST de la Ville',
            self::CPAS => 'Accès au PST du Cpas',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ADMIN => 'tabler-user-bolt',
            self::MANDATAIRE => 'tabler-user-circle',
            self::VILLE => 'tabler-building',
            self::CPAS => 'tabler-building-community',
        };
    }
}
