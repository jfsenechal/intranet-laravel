<?php

declare(strict_types=1);

namespace AcMarche\Pst\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ActionStateEnum: string implements HasColor, HasIcon, HasLabel
{
    case START = 'START';
    case PENDING = 'PENDING';
    case FINISHED = 'FINISHED';
    case SUSPENDED = 'SUSPENDED';

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
            self::START => 'A démarrer',
            self::PENDING => 'En cours',
            self::SUSPENDED => 'Suspendu',
            self::FINISHED => 'Terminé',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::START => 'secondary',
            self::FINISHED => 'primary',
            self::PENDING => 'success',
            self::SUSPENDED => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::START => 'heroicon-m-check',
            self::FINISHED => 'tabler-bolt-filled',
            self::PENDING => 'tabler-line-dashed',
            self::SUSPENDED => 'tabler-bolt-off',
        };
    }
}
