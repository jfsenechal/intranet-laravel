<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum OrganizersEnum: string implements HasLabel
{
    case VILLE = 'Organisé par la Ville';
    case VILLE_PARTNER = 'Marche partenaire';
    case EXTERNAL = 'Organisateur externe';

    public function getLabel(): string|Htmlable|null
    {
        return $this->value;
    }
}
