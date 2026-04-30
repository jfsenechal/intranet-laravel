<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum TrainingTypeEnum: string implements HasLabel
{
    case TYPE1 = 'type1';
    case TYPE2 = 'type2';
    case TYPE3 = 'type3';

    public function getLabel(): string
    {
        return match ($this) {
            self::TYPE1 => 'Type 1',
            self::TYPE2 => 'Type 2',
            self::TYPE3 => 'Type 3',
        };
    }
}
