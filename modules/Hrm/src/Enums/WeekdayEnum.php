<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum WeekdayEnum: int implements HasLabel
{
    case Lundi = 1;
    case Mardi = 2;
    case Mercredi = 3;
    case Jeudi = 4;
    case Vendredi = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::Lundi => 'Lundi',
            self::Mardi => 'Mardi',
            self::Mercredi => 'Mercredi',
            self::Jeudi => 'Jeudi',
            self::Vendredi => 'Vendredi',
        };
    }
}
