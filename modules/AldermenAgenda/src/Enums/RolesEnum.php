<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_INVITATION = 'ROLE_INVITATION';

    public function getLabel(): string|Htmlable|null
    {
        switch ($this) {
            case self::ROLE_INVITATION:
                'Invitations';
            default:
                return null;
        }
    }
}
