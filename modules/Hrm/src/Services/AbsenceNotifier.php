<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Services;

use AcMarche\Hrm\Models\Absence;
use Carbon\CarbonImmutable;

final class AbsenceNotifier
{
    private const int CESI_THRESHOLD_DAYS = 28;

    private const int WORK_POTENTIAL_THRESHOLD_DAYS = 56;

    public function getProximityAlert(Absence $absence): ?string
    {
        $previous = $this->findPreviousAbsence($absence);
        if (! $previous || ! $previous->end_date || ! $absence->end_date) {
            return null;
        }

        $diffDays = (int) $previous->end_date->diffInDays($absence->end_date, true);
        $eightWeeksInDays = self::WORK_POTENTIAL_THRESHOLD_DAYS;

        if ($diffDays > $eightWeeksInDays) {
            return null;
        }

        $weeks = intdiv($diffDays, 7);
        $days = $diffDays % 7;

        return sprintf(
            'Attention : cette absence est enregistrée %d semaine(s) et %d jour(s) après la précédente (du %s). Délai inférieur à 8 semaines.',
            $weeks,
            $days,
            $previous->end_date->format('d-m-Y'),
        );
    }

    public function getCesiAlert(Absence $absence): ?string
    {
        $days = $this->currentDurationInDays($absence);
        if ($days === null || $days < self::CESI_THRESHOLD_DAYS) {
            return null;
        }

        return sprintf(
            'Cette absence dure %d jour(s) (≥ 4 semaines). Un encodage sur le site du CESI est nécessaire.',
            $days,
        );
    }

    public function getWorkPotentialAlert(Absence $absence): ?string
    {
        $days = $this->currentDurationInDays($absence);
        if ($days === null || $days < self::WORK_POTENTIAL_THRESHOLD_DAYS) {
            return null;
        }

        $weeks = intdiv($days, 7);

        return sprintf(
            'Cette absence dure %d semaine(s) (≥ 8 semaines). Une demande de "Potentiel de travail" au CESI est nécessaire.',
            $weeks,
        );
    }

    public function findPreviousAbsence(Absence $absence): ?Absence
    {
        if (! $absence->employee_id || ! $absence->start_date) {
            return null;
        }

        return Absence::query()
            ->where('employee_id', $absence->employee_id)
            ->where('id', '!=', $absence->id)
            ->whereNotNull('end_date')
            ->where('end_date', '<', $absence->start_date)
            ->orderByDesc('end_date')
            ->first();
    }

    private function currentDurationInDays(Absence $absence): ?int
    {
        if (! $absence->start_date) {
            return null;
        }

        $endDate = $absence->end_date ?? CarbonImmutable::now();

        return (int) $absence->start_date->diffInDays($endDate, true) + 1;
    }
}
