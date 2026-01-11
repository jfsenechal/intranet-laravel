<?php

namespace AcMarche\App\Enums;

enum DepartmentEnum: string
{
    case VILLE = 'Ville';
    case CPAS = 'Cpas';

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $actionStateEnum) {
            $values[] = $actionStateEnum->value;
        }

        return $values;
    }
}
