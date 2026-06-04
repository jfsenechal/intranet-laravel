<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Enums;

use Filament\Support\Contracts\HasLabel;

enum ServicesEnum: string implements HasLabel
{
    case ETAT_CIVIL = 'État civil';
    case POPULATION = 'Population';
    case ETRANGERS = 'Étrangers';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function color(): string
    {
        return match ($this) {
            self::ETAT_CIVIL => '#FFAFCC',
            self::POPULATION => '#52B69A',
            self::ETRANGERS => '#A2D2FF',
        };
    }
}
