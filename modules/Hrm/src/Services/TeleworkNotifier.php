<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Services;

use AcMarche\Hrm\Mail\TeleworkEmployeeHrResultMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeManagerResultMail;
use AcMarche\Hrm\Mail\TeleworkHrValidationMail;
use AcMarche\Hrm\Mail\TeleworkManagerValidationMail;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use Illuminate\Support\Facades\Mail;

final class TeleworkNotifier
{
    public static function employee(Telework $telework): ?Employee
    {
        return Employee::query()->where('username', $telework->user_add)->first();
    }

    public static function direction(Employee $employee): ?Direction
    {
        return $employee->contracts()->active()->first()?->direction;
    }

    public static function director(Employee $employee): ?Employee
    {
        $direction = self::direction($employee);

        if (! $direction instanceof Direction || empty($direction->director)) {
            return null;
        }

        return Employee::query()->where('username', $direction->director)->first();
    }

    public static function notifyManagerOfNewRequest(Telework $telework): void
    {
        $employee = self::employee($telework);
        if (! $employee instanceof Employee) {
            return;
        }

        $director = self::director($employee);
        if (! $director instanceof Employee || empty($director->professional_email)) {
            return;
        }

        Mail::to($director->professional_email)
            ->send(new TeleworkManagerValidationMail($telework, $employee, $director));
    }

    public static function notifyEmployeeAfterManagerValidation(Telework $telework): void
    {
        $employee = self::employee($telework);
        if (! $employee instanceof Employee || empty($employee->professional_email)) {
            return;
        }

        Mail::to($employee->professional_email)
            ->send(new TeleworkEmployeeManagerResultMail($telework, $employee));
    }

    public static function notifyHrTeam(Telework $telework): void
    {
        $recipients = config('hrm.team_emails', []);

        if ($recipients === []) {
            return;
        }

        $employee = self::employee($telework);

        Mail::to($recipients)
            ->send(new TeleworkHrValidationMail($telework, $employee));
    }

    public static function notifyEmployeeAfterHrValidation(Telework $telework): void
    {
        $employee = self::employee($telework);
        if (! $employee instanceof Employee || empty($employee->professional_email)) {
            return;
        }

        Mail::to($employee->professional_email)
            ->send(new TeleworkEmployeeHrResultMail($telework, $employee));
    }
}
