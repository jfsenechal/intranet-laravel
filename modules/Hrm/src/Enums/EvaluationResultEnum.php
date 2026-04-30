<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

enum EvaluationResultEnum: string implements \Filament\Support\Contracts\HasLabel
{
    case EXCELLENT = 'Excellent';
    case VERY_POSITIVE = 'Très positive';
    case NONE = 'Néant';
    case POSITIVE = 'Positive';
    case SATISFACTORY = 'Satisfaisante';
    case NEEDS_IMPROVEMENT = 'A améliorer';
    case INSUFFICIENT = 'Insuffisante';

    public function getLabel(): string
    {
        return $this->value;
    }
}
