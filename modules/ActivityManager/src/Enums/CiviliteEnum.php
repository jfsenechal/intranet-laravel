<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Enums;

use Filament\Support\Contracts\HasLabel;

enum CiviliteEnum: string implements HasLabel
{
    case MADAME = 'Madame';
    case MONSIEUR = 'Monsieur';

    public function getLabel(): string
    {
        return $this->value;
    }
}
