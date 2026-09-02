<?php

declare(strict_types=1);

namespace AcMarche\App\Enums;

enum SignatureEnum: string
{
    case ADL = 'adl.png';
    case ALE = 'ale.jpg';
    case MARCHE = 'marche.jpg';
    case MDT = 'mtfa.webp';
    case CPAS = 'cpas.jpg';
    case CSL = 'csl.jpg';
    case MDR = 'mdr.jpg';
    case FAM = 'fam.jpg';
    case ESQUARE = 'esquareLogo.jpg';

    /**
     * Resolve a stored file name to its case, falling back to the commune logo when
     * a case has been renamed and the stored value matches none of them. Without
     * this a renamed case makes every read of the column throw a ValueError.
     */
    public static function fromFileName(?string $fileName): ?self
    {
        if ($fileName === null || $fileName === '') {
            return null;
        }

        return self::tryFrom($fileName) ?? self::MARCHE;
    }

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
            SignatureEnum::ESQUARE => 'e-Square',
            default => 'Ville de Marche-en-Famenne'
        };
    }
}
