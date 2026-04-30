<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum LocationTypeEnum: int implements HasLabel
{
    case Domicile = 1;
    case AutreLieu = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Domicile => 'Domicile',
            self::AutreLieu => 'Autre lieu',
        };
    }
}
