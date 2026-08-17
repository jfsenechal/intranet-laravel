<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Absences\Pages\EditAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ViewAbsence;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\EditContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\EditDeadline;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\ViewDeadline;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\EditDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ViewDiploma;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\AbsencesRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ContractsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DeadlinesRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DiplomasRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\TrainingsRelationManager;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\EditTraining;
use AcMarche\Hrm\Filament\Resources\Trainings\Pages\ViewTraining;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Training;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

dataset('relation managers with a view page', [
    'absences' => [
        AbsencesRelationManager::class,
        fn (Employee $employee): Model => Absence::factory()->create(['employee_id' => $employee->id]),
        ViewAbsence::class,
        EditAbsence::class,
    ],
    'contracts' => [
        ContractsRelationManager::class,
        fn (Employee $employee): Model => Contract::factory()->create(['employee_id' => $employee->id]),
        ViewContract::class,
        EditContract::class,
    ],
    'deadlines' => [
        DeadlinesRelationManager::class,
        fn (Employee $employee): Model => Deadline::factory()->create(['employee_id' => $employee->id]),
        ViewDeadline::class,
        EditDeadline::class,
    ],
    'diplomas' => [
        DiplomasRelationManager::class,
        fn (Employee $employee): Model => Diploma::factory()->create(['employee_id' => $employee->id]),
        ViewDiploma::class,
        EditDiploma::class,
    ],
    'trainings' => [
        TrainingsRelationManager::class,
        fn (Employee $employee): Model => Training::factory()->create(['employee_id' => $employee->id]),
        ViewTraining::class,
        EditTraining::class,
    ],
]);

it(
    'links rows and the view action to the record view page instead of a modal',
    function (string $relationManager, Closure $createRecord, string $viewPage): void {
        $employee = Employee::factory()->create();
        $record = $createRecord($employee);
        $url = $viewPage::getUrl(['record' => $record], panel: 'hrm-panel');

        $component = livewire($relationManager, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertTableActionHasUrl('view', $url, $record);

        expect($component->instance()->getTable()->getRecordUrl($record))->toBe($url);
    },
)->with('relation managers with a view page');

it(
    'links the edit action to the record edit page instead of a modal',
    function (string $relationManager, Closure $createRecord, string $viewPage, string $editPage): void {
        $employee = Employee::factory()->create();
        $record = $createRecord($employee);

        livewire($relationManager, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertTableActionHasUrl('edit', $editPage::getUrl(['record' => $record], panel: 'hrm-panel'), $record);
    },
)->with('relation managers with a view page');
