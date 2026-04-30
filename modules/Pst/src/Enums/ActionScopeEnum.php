<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ActionScopeEnum: string implements HasColor, HasIcon, HasLabel
{
    case INTERNAL = 'INTERNAL';
    case EXTERNAL = 'EXTERNAL';

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
            self::INTERNAL => 'Interne',
            self::EXTERNAL => 'Externe',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INTERNAL => 'secondary',
            self::EXTERNAL => 'primary',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::INTERNAL => Heroicon::Home,
            self::EXTERNAL => Heroicon::GlobeAlt,
        };
    }
}
