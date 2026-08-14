<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands\Concerns;

use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * Department scoping and mailing shared by the reminder commands, which all run
 * once per department and report to that department's recipients.
 */
trait SendsDepartmentReminders
{
    /**
     * Filament resource URLs are panel-scoped. There is no "current panel" in a
     * console context, so resolve them against the HRM panel that owns these
     * resources instead of the default panel.
     */
    private function useHrmPanel(): void
    {
        Filament::setCurrentPanel('hrm-panel');
    }

    /**
     * @return list<string>
     */
    private function recipientsFor(string $department): array
    {
        return array_values((array) config("hrm.reminders.recipients.{$department}", []));
    }

    /**
     * Build the employer set for a department: the root employer (matched by
     * slug) plus all of its direct children.
     *
     * @return list<int>
     */
    private function employerIdsFor(string $department): array
    {
        $root = Employer::query()->where('slug', $department)->first();

        if (! $root instanceof Employer) {
            return [];
        }

        return Employer::query()
            ->where('id', $root->id)
            ->orWhere('parent_id', $root->id)
            ->orderBy('name')
            ->pluck('id')
            ->all();
    }

    /**
     * Restrict an employee query to the given employer set.
     *
     * The department an employee belongs to is read from their active contracts
     * only: someone who moved between departments keeps their old contracts, and
     * those must not pull their reminders back into the department they left.
     * When no active contract answers the question, the department is unknown
     * rather than none, so the employee is kept in every department run instead
     * of losing the reminder altogether.
     *
     * @param  list<int>  $employerIds
     */
    private function scopeEmployeeToEmployers(Builder $employee, array $employerIds): void
    {
        $employee->where(function (Builder $query) use ($employerIds): void {
            $query->whereHas('activeContracts', function (Builder $contracts) use ($employerIds): void {
                $contracts->whereIn('employer_id', $employerIds);
            })->orWhereDoesntHave('activeContracts');
        });
    }

    /**
     * Restrict a query to records whose employee belongs to the given employer
     * set.
     *
     * @param  list<int>  $employerIds
     */
    private function whereEmployeeBelongsToEmployers(Builder $query, array $employerIds): void
    {
        $query->whereHas('employee', function (Builder $employee) use ($employerIds): void {
            $this->scopeEmployeeToEmployers($employee, $employerIds);
        });
    }

    /**
     * @param  list<string>  $recipients
     * @param  bool  $withRecordName  Adds the record's own name to the mail subject
     */
    private function dispatchMail(array $recipients, string $reminderType, Model $record, string $url, ?Employee $employee, bool $withRecordName = false): void
    {
        Mail::to($recipients)->send(new ReminderMail(
            reminderType: $reminderType,
            record: $record,
            url: $url,
            employeeName: $employee instanceof Employee
                ? mb_trim($employee->last_name.' '.$employee->first_name)
                : null,
            recordName: $withRecordName
                ? mb_trim((string) $record->getAttribute('name'))
                : null,
        ));
    }
}
