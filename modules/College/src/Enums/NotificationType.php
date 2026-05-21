<?php

declare(strict_types=1);

namespace AcMarche\College\Enums;

use Filament\Support\Contracts\HasLabel;

enum NotificationType: string implements HasLabel
{
    case Ordre = 'ordre';
    case Pv = 'pv';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ordre => 'Ordre du jour',
            self::Pv => 'Procès-verbal',
        };
    }

    /**
     * Recipient column flagging who must receive the "Collège" document.
     */
    public function collegeColumn(): string
    {
        return match ($this) {
            self::Ordre => 'ordre_college',
            self::Pv => 'pv_college',
        };
    }

    /**
     * Recipient column flagging who must receive the "Service" document.
     */
    public function serviceColumn(): string
    {
        return match ($this) {
            self::Ordre => 'ordre_service',
            self::Pv => 'pv_service',
        };
    }

    public function collegeFileLabel(): string
    {
        return match ($this) {
            self::Ordre => 'Convocation Collège',
            self::Pv => 'PV Collège',
        };
    }

    public function serviceFileLabel(): string
    {
        return match ($this) {
            self::Ordre => 'Convocation Services',
            self::Pv => 'PV Services',
        };
    }

    public function collegeFileHelp(): string
    {
        return match ($this) {
            self::Ordre => 'Lettre de convoc + OJ + PDel + PA',
            self::Pv => 'PV complet',
        };
    }

    public function serviceFileHelp(): string
    {
        return match ($this) {
            self::Ordre => 'OJ',
            self::Pv => 'PV pour service',
        };
    }
}
