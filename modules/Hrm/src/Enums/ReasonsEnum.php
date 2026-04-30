<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReasonsEnum: string implements HasLabel
{
    case ACCIDENT = 'accident';
    case CIRCUMSTANCE = 'circonstance';
    case CIRCUMSTANCE_SICKNESS = 'circonstance maladie';
    case CIRCUMSTANCE_SICKNESS_ONE_DAY = 'circonstance maladie 1 jour';
    case CIRCUMSTANCE_SICKNESS_HALF_DAY = 'circonstance maladie 1/2 jour';
    case EXCEPTIONAL_LEAVE = 'congés exceptionnels';
    case DISPENSATION = 'dispense';
    case SICKNESS = 'maladie';
    case SICKNESS_WORK_ACCIDENT_CONVENTION = 'maladie acc conv';
    case SICKNESS_RETURN = 'maladie retour';
    case MATERNITY = 'maternité';
    case PATERNITY = 'paternité';
    case RELAPSE = 'rechute';
    case BLOOD_DONATION = 'sang';
    case WITHOUT_CERTIFICATE = 'sans certificat';
    case UNION = 'syndicat';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACCIDENT => 'Accident',
            self::CIRCUMSTANCE => 'Circonstance',
            self::CIRCUMSTANCE_SICKNESS => 'Circonstance maladie',
            self::CIRCUMSTANCE_SICKNESS_ONE_DAY => 'Circonstance maladie 1 jour',
            self::CIRCUMSTANCE_SICKNESS_HALF_DAY => 'Circonstance maladie 1/2 jour',
            self::EXCEPTIONAL_LEAVE => 'Congés exceptionnels',
            self::DISPENSATION => 'Dispense',
            self::SICKNESS => 'Maladie',
            self::SICKNESS_WORK_ACCIDENT_CONVENTION => 'Maladie Acc Conv',
            self::SICKNESS_RETURN => 'Maladie Retour',
            self::MATERNITY => 'Maternité',
            self::PATERNITY => 'Paternité',
            self::RELAPSE => 'Rechute',
            self::BLOOD_DONATION => 'Sang',
            self::WITHOUT_CERTIFICATE => 'Sans Certificat',
            self::UNION => 'Syndicat',
        };
    }
}
