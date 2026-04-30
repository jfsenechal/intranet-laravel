<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum DayTypeEnum: int implements HasLabel
{
    case Fixe = 1;
    case Variable = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Fixe => 'Jour fixe',
            self::Variable => 'Jour variable',
        };
    }
}
