<?php

declare(strict_types=1);

namespace AcMarche\App\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum DepartmentEnum: string implements HasColor, HasIcon, HasLabel
{
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
            self::CPAS => 'Cpas',
            self::VILLE => 'Ville',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CPAS => 'primary',
            self::VILLE => 'success',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::CPAS => Heroicon::Heart,
            self::VILLE => Heroicon::BuildingOffice2,
        };
    }
}
