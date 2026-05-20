<?php

namespace AcMarche\AldermenAgenda\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum EventTypesEnum: string implements HasLabel
{
    case INVITATION = 'Invitation';
    case INFORMATION = 'Information';

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }
}
