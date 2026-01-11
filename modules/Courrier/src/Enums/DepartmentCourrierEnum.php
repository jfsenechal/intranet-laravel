<?php

namespace AcMarche\Courrier\Enums;

enum DepartmentCourrierEnum:string
{
    case BGM = 'Bgm';
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
