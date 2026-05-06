<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RolesEnum: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case PST = 'ROLE_PST';
    case ADMIN = 'ROLE_PST_ADMIN';
    case MANDATAIRE = 'ROLE_PST_MANDATAIRE';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrateur PST',
            self::MANDATAIRE => 'Mandataire',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ADMIN => 'success',
            self::MANDATAIRE => 'primary',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ADMIN => 'Gestion des actions,des agents et des paramètres',
            self::MANDATAIRE => 'Accès en lecture seul',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ADMIN => 'tabler-user-bolt',
            self::MANDATAIRE => 'tabler-user-circle',
        };
    }
}
