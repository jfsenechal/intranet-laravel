<?php

declare(strict_types=1);

namespace App\Enums;

enum SignatureEnum: string
{
    case ADL = 'adl.png';
    case ALE = 'ale.jpg';
    case MARCHE = 'marche.jpg';
    case MDT = 'mdt.jpg';
    case CPAS = 'cpas.jpg';
    case CSL = 'csl.jpg';
    case MDR = 'mdr.jpg';
    case FAM = 'fam.jpg';
    case ESQUARE = 'esquareLogo.jpg';

    public function getTitle(): string
    {
        return match ($this) {
            SignatureEnum::ADL => 'Agence de Développement Local',
            SignatureEnum::ALE => 'Agence Local pour l\'emploi',
            SignatureEnum::MDT => 'Maison du Tourisme du Pays de Marche & Nassogne',
            SignatureEnum::MDR => 'Maison de Repos Home Libert',
            SignatureEnum::CPAS => 'Cpas',
            SignatureEnum::CSL => 'Centre sportif local',
            SignatureEnum::FAM => 'Famenne & Art Museum',
            SignatureEnum::ESQUARE => 'E-square',
            default => 'Ville de Marche-en-Famenne'
        };
    }
}
