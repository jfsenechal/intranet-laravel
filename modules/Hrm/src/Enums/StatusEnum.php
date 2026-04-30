<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasLabel
{
    case AGENT = 'Agent';
    case APPLICATION = 'Candidature';
    case RESIGNED = 'Démission';
    case STUDENT = 'Etudiant';
    case ENDED = 'Fin';
    case CONTRACT_ENDED = 'Fin de contrat';
    case TERMINATED = 'Licenciement';
    case RETIRED = 'Pension';
    case INTERN = 'Stagiaire';

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public function getLabel(): string
    {
        return $this->value;
    }
}
