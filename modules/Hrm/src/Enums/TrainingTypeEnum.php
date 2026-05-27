<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum TrainingTypeEnum: string implements HasDescription, HasLabel
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

    public function getDescription(): string
    {
        return match ($this) {
            self::TYPE1 => "Obligation de suivre une formation supplémentaire de 40 périodes pour pouvoir bénéficier de l'évolution de carrière. L'employeur prend en charge les frais d'inscriptions et les heures de formations ainsi que les frais de déplacement.",
            self::TYPE2 => "Possibilité de suivre des formations nécessaires pour pouvoir bénéficier de l'évolution de carrière et d'appliquer le principe 80/20. L'employeur prend en charge à concurrence de la moitié les frais d'inscription et la moitié les heures de formations et ne prend pas en charge les frais de déplacement.",
            self::TYPE3 => "Possibilité de suivre des formations continues pour permettre aux agents de maintenir leurs connaissances à niveau afin d'assurer la continuité de leur fonction et améliorer la qualité du service public. L'employeur prend en charge les frais d'inscriptions, les heures de formations ainsi que les frais de déplacement.",
        };
    }
}
