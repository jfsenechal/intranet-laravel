<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum InternTypeEnum: string implements HasLabel
{
    case Internship = 'Décision Collège';
    case Spontaneous = 'Demande spontanée';

    public function getLabel(): string
    {
        return match ($this) {
            self::Internship => self::Internship->value,
            self::Spontaneous => self::Spontaneous->value,
        };
    }
}
