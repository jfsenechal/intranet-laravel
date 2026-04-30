<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActionTypeEnum: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case PST = 'PST';
    case PERENNIAL = 'PERENNIAL';
    case OFF_SCREEN = 'OFF_SCREEN';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }

    public static function findByName(string $name): ?self
    {
        return match ($name) {
            'PST' => self::PST,
            'Perenne' => self::PERENNIAL,
            'Hors Champ' => self::OFF_SCREEN,
            default => null,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PST => 'PST',
            self::PERENNIAL => 'Perenne',
            self::OFF_SCREEN => 'Hors champ',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PST => 'success',
            self::PERENNIAL => 'warning',
            self::OFF_SCREEN => 'primary',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::PST => 'danger',
            self::PERENNIAL => 'success',
            self::OFF_SCREEN => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PST => 'heroicon-m-clock',
            self::PERENNIAL => 'tabler-eye',
            self::OFF_SCREEN => 'heroicon-m-exclamation-circle',
        };
    }
}
