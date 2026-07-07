<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Enums;

use Filament\Support\Contracts\HasLabel;

enum FicheTypeEnum: string implements HasLabel
{
    case DEFAULT = 'default';

    case ABSENCE = 'absence';

    case LEGISLATION = 'legislation';

    public function getLabel(): string
    {
        return match ($this) {
            self::DEFAULT => 'Standard',
            self::ABSENCE => 'Absence',
            self::LEGISLATION => 'Législation',
        };
    }
}
