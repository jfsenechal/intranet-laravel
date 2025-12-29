<?php

namespace AcMarche\Security\Constant;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DepartmentEnum: string implements HasColor, HasIcon, HasLabel
{
    case COMMON = 'COMMON';
    case CPAS = 'CPAS';
    case VILLE = 'VILLE';

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
            self::COMMON => 'Cpas et Ville',
            self::CPAS => 'Cpas',
            self::VILLE => 'Ville',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::COMMON => 'success',
            self::CPAS => 'primary',
            self::VILLE => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::COMMON => 'tabler-cell-signal-4',
            self::CPAS => 'tabler-cell-signal-2',
            self::VILLE => 'tabler-cell-signal-5',
        };
    }
}
