<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ActionSynergyEnum: string implements HasColor, HasIcon, HasLabel
{
    case YES = 'YES';
    case NO = 'NO';

    public static function getTitle(): string
    {
        return 'Synergie Ville/Cpas';
    }

    public static function getDescription(): string
    {
        return 'Si oui, l\'action est mise en commun';
    }

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
            self::YES => 'Oui',
            self::NO => 'Non',
            default => 'Non déterminé'
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YES => 'success',
            self::NO => 'warning',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::YES => Heroicon::Heart,
            self::NO => 'tabler-heart-off',
        };
    }
}
