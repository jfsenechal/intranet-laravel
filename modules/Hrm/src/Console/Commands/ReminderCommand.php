<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ViewAbsence;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\ViewDeadline;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ViewSmsReminder;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\Internship;
use AcMarche\Hrm\Models\SmsReminder;
use AcMarche\Hrm\Models\Training;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hrm:reminders {department : ville|cpas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all daily reminders to the requested department';

    public function handle(): int
    {
        $department = (string) $this->argument('department');
        $recipients = (array) config("hrm.reminders.recipients.{$department}", []);

        if ($recipients === []) {
            $this->error("No recipients configured for department '{$department}'.");

            return SfCommand::FAILURE;
        }

        $employerIds = $this->employerIdsFor($department);

        if ($employerIds === []) {
            $this->info("No employers found for department '{$department}'.");

            return SfCommand::SUCCESS;
        }

        $today = Carbon::today();

        $this->sendAbsences($today, $employerIds, $recipients);
        $this->sendDeadlines($today, $employerIds, $recipients);
        $this->sendContracts($today, $employerIds, $recipients);
        $this->sendStudentReminders($today, $employerIds, $recipients);
        $this->sendEvaluations($today, $employerIds, $recipients);
        $this->sendEvolutions($today, $employerIds, $recipients);
        $this->sendTrainings($today, $employerIds, $recipients);
        $this->sendSmsReminders($today, $employerIds, $recipients);
        $this->sendInternships($today, $employerIds, $recipients);

        return SfCommand::SUCCESS;
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
     * Restrict a query to records whose employee has at least one active
     * contract within the given employer set.
     *
     * @param  list<int>  $employerIds
     */
    private function whereEmployeeHasActiveContract(Builder $query, array $employerIds): void
    {
        $query->whereHas('employee.activeContracts', function (Builder $contracts) use ($employerIds): void {
            $contracts->whereIn('employer_id', $employerIds);
        });
    }

    /**
     * @param  list<string>  $recipients
     */
    private function dispatchMail(array $recipients, string $reminderType, Model $record, string $url, ?Employee $employee): void
    {
        Mail::to($recipients)->send(new ReminderMail(
            reminderType: $reminderType,
            record: $record,
            url: $url,
            employeeName: $employee instanceof Employee
                ? mb_trim($employee->last_name.' '.$employee->first_name)
                : null,
        ));
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendAbsences(Carbon $today, array $employerIds, array $recipients): void
    {
        Absence::query()
            ->whereDate('reminder_date', $today)
            ->tap(fn (Builder $query) => $this->whereEmployeeHasActiveContract($query, $employerIds))
            ->with('employee')
            ->get()
            ->each(function (Absence $absence) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Absence',
                    $absence,
                    ViewAbsence::getUrl(['record' => $absence]),
                    $absence->employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendDeadlines(Carbon $today, array $employerIds, array $recipients): void
    {
        Deadline::query()
            ->whereDate('reminder_date', $today)
            ->tap(fn (Builder $query) => $this->whereEmployeeHasActiveContract($query, $employerIds))
            ->with('employee')
            ->get()
            ->each(function (Deadline $deadline) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Échéance',
                    $deadline,
                    ViewDeadline::getUrl(['record' => $deadline]),
                    $deadline->employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendContracts(Carbon $today, array $employerIds, array $recipients): void
    {
        Contract::query()
            ->whereDate('reminder_date', $today)
            ->tap(fn (Builder $query) => $this->whereEmployeeHasActiveContract($query, $employerIds))
            ->with('employee')
            ->get()
            ->each(function (Contract $contract) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Contrat',
                    $contract,
                    ViewContract::getUrl(['record' => $contract]),
                    $contract->employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendStudentReminders(Carbon $today, array $employerIds, array $recipients): void
    {
        Employee::query()
            ->where('status', StatusEnum::STUDENT)
            ->whereDate('reminder_date', $today)
            ->whereHas('activeContracts', function (Builder $contracts) use ($employerIds): void {
                $contracts->whereIn('employer_id', $employerIds);
            })
            ->get()
            ->each(function (Employee $employee) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Étudiant',
                    $employee,
                    ViewEmployee::getUrl(['record' => $employee]),
                    $employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendEvaluations(Carbon $today, array $employerIds, array $recipients): void
    {
        Evaluation::query()
            ->whereDate('next_evaluation_date', $today)
            ->whereHas('employee', function (Builder $employee) use ($employerIds): void {
                $employee->where('status', StatusEnum::AGENT)
                    ->whereHas('activeContracts', function (Builder $contracts) use ($employerIds): void {
                        $contracts->whereIn('employer_id', $employerIds);
                    });
            })
            ->with('employee')
            ->get()
            ->each(function (Evaluation $evaluation) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Évaluation',
                    $evaluation,
                    ViewEvaluation::getUrl(['record' => $evaluation]),
                    $evaluation->employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendEvolutions(Carbon $today, array $employerIds, array $recipients): void
    {
        Employee::query()
            ->where('status', StatusEnum::AGENT)
            ->whereDate('reminder_date', $today)
            ->whereHas('activeContracts', function (Builder $contracts) use ($employerIds): void {
                $contracts->whereIn('employer_id', $employerIds);
            })
            ->get()
            ->each(function (Employee $employee) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Évolution',
                    $employee,
                    ViewEmployee::getUrl(['record' => $employee]),
                    $employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendTrainings(Carbon $today, array $employerIds, array $recipients): void
    {
        Training::query()
            ->whereDate('reminder_date', $today)
            ->tap(fn (Builder $query) => $this->whereEmployeeHasActiveContract($query, $employerIds))
            ->with('employee')
            ->get()
            ->each(function (Training $training) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Formation',
                    $training,
                    ViewTraining::getUrl(['record' => $training]),
                    $training->employee,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendSmsReminders(Carbon $today, array $employerIds, array $recipients): void
    {
        SmsReminder::query()
            ->where(function (Builder $query) use ($today): void {
                $query->whereDate('reminder_date', $today)
                    ->orWhereDate('other_reminder_date', $today);
            })
            ->tap(fn (Builder $query) => $this->whereEmployeeHasActiveContract($query, $employerIds))
            ->with('employee')
            ->get()
            ->each(function (SmsReminder $sms) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'SMS',
                    $sms,
                    ViewSmsReminder::getUrl(['record' => $sms]),
                    $sms->employee,
                );

                $sms->forceFill([
                    'sent_at' => Carbon::now(),
                    'result' => 'sent to '.implode(', ', $recipients),
                ])->save();
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendInternships(Carbon $today, array $employerIds, array $recipients): void
    {
        Internship::query()
            ->whereDate('reminder_date', $today)
            ->whereHas('employee', function (Builder $employee) use ($employerIds): void {
                $employee->where('is_archived', false)
                    ->whereHas('activeContracts', function (Builder $contracts) use ($employerIds): void {
                        $contracts->whereIn('employer_id', $employerIds);
                    });
            })
            ->with('employee')
            ->get()
            ->each(function (Internship $internship) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Stage',
                    $internship,
                    ViewEmployee::getUrl(['record' => $internship->employee]),
                    $internship->employee,
                );
            });
    }
}
