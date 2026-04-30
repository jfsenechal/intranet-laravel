<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum WorkCapacityAssessmentEnum: string implements HasLabel
{
    case NOT_REQUIRED = 'not_required';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::NOT_REQUIRED => 'Non nécessaire',
        };
    }
}
