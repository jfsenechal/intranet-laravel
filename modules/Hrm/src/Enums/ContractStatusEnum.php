<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContractStatusEnum: string implements HasLabel
{
    case EMPLOYEE = 'employe';
    case WORKER = 'ouvrier';

    public function getLabel(): string
    {
        return match ($this) {
            self::WORKER => 'Ouvrier',
            self::EMPLOYEE => 'Employé',
        };
    }
}
