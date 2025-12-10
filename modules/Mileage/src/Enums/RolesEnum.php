<?php

namespace AcMarche\Mileage\Enums;

use Exception;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum RolesEnum: string implements HasLabel
{
    case ROLE_FINANCE_DEPLACEMENT_ADMIN = 'ROLE_FINANCE_DEPLACEMENT_ADMIN';
    case ROLE_FINANCE_DEPLACEMENT_VILLE = 'ROLE_FINANCE_DEPLACEMENT_VILLE';
    case ROLE_FINANCE_DEPLACEMENT_CPAS = 'ROLE_FINANCE_DEPLACEMENT_CPAS';

    public static function getRoles(): array
    {
        return array_values(self::cases());
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ROLE_FINANCE_DEPLACEMENT_ADMIN => throw new Exception('To be implemented'),
            self::ROLE_FINANCE_DEPLACEMENT_VILLE => throw new Exception('To be implemented'),
            self::ROLE_FINANCE_DEPLACEMENT_CPAS => throw new Exception('To be implemented'),
            default => null
        };
    }
}
