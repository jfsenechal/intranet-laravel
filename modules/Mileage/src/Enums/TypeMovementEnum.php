<?php

namespace AcMarche\Mileage\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

enum TypeMovementEnum: string implements HasLabel
{
    case EXTERNAL = 'externe';
    case INTERNAL = 'interne';

    public function getLabel(): string|Htmlable|null
    {
        return Str::title($this->value);
    }
}
