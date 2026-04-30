<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum YesOrNoEnum: int implements HasColor, HasIcon, HasLabel
{
    case YES = 1;
    case NO = 0;

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
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::YES => 'success',
            self::NO => 'primary',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::YES => Heroicon::Check,
            self::NO => Heroicon::XMark,
        };
    }
}
