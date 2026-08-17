<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Console\Commands\Concerns\SendsDepartmentReminders;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ViewAbsence;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\ViewDeadline;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\Internship;
use AcMarche\Hrm\Models\Training;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Symfony\Component\Console\Command\Command as SfCommand;

final class ReminderCommand extends Command
{
    use SendsDepartmentReminders;

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
    protected $description = 'Send the daily mail reminders to the requested department (SMS reminders have their own command)';

    public function handle(): int
    {
        $department = (string) $this->argument('department');
        $recipients = $this->recipientsFor($department);

        if ($recipients === []) {
            $this->error("No recipients configured for department '{$department}'.");

            return SfCommand::FAILURE;
        }

        $this->useHrmPanel();

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
        $this->sendInternships($today, $employerIds, $recipients);

        return SfCommand::SUCCESS;
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendAbsences(Carbon $today, array $employerIds, array $recipients): void
    {
        Absence::query()
            ->whereDate('reminder_date', $today)
            ->tap(fn (Builder $query) => $this->whereEmployeeBelongsToEmployers($query, $employerIds))
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
            ->where(function (Builder $query) use ($employerIds): void {
                $this->whereEmployeeBelongsToEmployers($query, $employerIds);

                // Deadlines without an employee still belong to a department
                // through their own employer, so include those too.
                $query->orWhere(function (Builder $unassigned) use ($employerIds): void {
                    $unassigned->whereNull('employee_id')
                        ->whereIn('employer_id', $employerIds);
                });
            })
            ->with('employee')
            ->get()
            ->each(function (Deadline $deadline) use ($recipients): void {
                $this->dispatchMail(
                    $recipients,
                    'Échéance',
                    $deadline,
                    ViewDeadline::getUrl(['record' => $deadline]),
                    $deadline->employee,
                    withRecordName: true,
                );
            });
    }

    /**
     * @param  list<int>  $employerIds
     * @param  list<string>  $recipients
     */
    private function sendContracts(Carbon $today, array $employerIds, array $recipients): void
    {
        // A contract carries its own employer, so it is scoped on that rather
        // than on the employee: an employee who moved between departments still
        // has contracts on both sides, and each one belongs to a single
        // department.
        Contract::query()
            ->whereDate('reminder_date', $today)
            ->whereIn('employer_id', $employerIds)
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
            ->tap(fn (Builder $query) => $this->scopeEmployeeToEmployers($query, $employerIds))
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
                $employee->where('status', StatusEnum::AGENT);

                $this->scopeEmployeeToEmployers($employee, $employerIds);
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
            ->tap(fn (Builder $query) => $this->scopeEmployeeToEmployers($query, $employerIds))
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
            ->tap(fn (Builder $query) => $this->whereEmployeeBelongsToEmployers($query, $employerIds))
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
    private function sendInternships(Carbon $today, array $employerIds, array $recipients): void
    {
        Internship::query()
            ->whereDate('reminder_date', $today)
            ->whereHas('employee', function (Builder $employee) use ($employerIds): void {
                $employee->where('is_archived', false);

                $this->scopeEmployeeToEmployers($employee, $employerIds);
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
