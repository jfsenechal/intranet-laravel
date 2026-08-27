<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\ReasonsEnum;
use AcMarche\Hrm\Filament\Exports\AbsenceExport;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\CreateAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\EditAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ListAbsences;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ViewAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Schemas\AbsenceCallouts;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Callout;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListAbsences::class)
            ->assertOk();
    });

    it('can render the create page with an employee in the query string', function (): void {
        $employee = Employee::factory()->create();

        Livewire::withQueryParams(['employee_id' => $employee->id])
            ->test(CreateAbsence::class)
            ->assertOk()
            ->assertNoRedirect();
    });

    it('redirects to the employees list when no employee is provided', function (): void {
        Livewire::test(CreateAbsence::class)
            ->assertRedirect(EmployeeResource::getUrl('index'))
            ->assertNotified();
    });

    it('can render the view page', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(ViewAbsence::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(EditAbsence::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('callouts authorization', function (): void {
    it('makes the alert callouts visible only to administrators', function (): void {
        $employee = Employee::factory()->create();
        $absence = Absence::factory()->create([
            'employee_id' => $employee->id,
            'start_date' => now()->subDays(60),
            'end_date' => now(),
        ]);

        $visibilityFor = fn (Absence $absence): array => array_map(
            fn (Callout $callout): bool => $callout->model($absence)->isVisible(),
            AbsenceCallouts::components(),
        );

        // Administrator (authenticated in beforeEach): the triggered CESI and
        // work-potential callouts are visible.
        [, $cesiForAdmin, $workPotentialForAdmin] = $visibilityFor($absence);

        expect($cesiForAdmin)->toBeTrue()
            ->and($workPotentialForAdmin)->toBeTrue();

        // Non-administrator: every callout is hidden regardless of the alerts.
        $this->actingAs(User::factory()->create(['is_administrator' => false]));

        expect($visibilityFor($absence))->each->toBeFalse();
    });
});

describe('crud operations', function (): void {
    it('creates an absence for the employee passed in the query string', function (): void {
        $employee = Employee::factory()->create();

        Livewire::withQueryParams(['employee_id' => $employee->id])
            ->test(CreateAbsence::class)
            ->fillForm([
                'reason' => ReasonsEnum::SICKNESS->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas(Absence::class, [
            'employee_id' => $employee->id,
            'reason' => ReasonsEnum::SICKNESS->value,
        ]);
    });

    it('can update an absence', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(EditAbsence::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'reason' => ReasonsEnum::SICKNESS->value,
                'is_closed' => true,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Absence::class, [
            'id' => $record->id,
            'reason' => ReasonsEnum::SICKNESS->value,
            'is_closed' => 1,
        ]);
    });
});

describe('model behavior', function (): void {
    it('casts reason to ReasonsEnum', function (): void {
        $absence = Absence::factory()->create(['reason' => ReasonsEnum::SICKNESS->value]);

        expect($absence->reason)->toBe(ReasonsEnum::SICKNESS);
    });

    it('casts is_closed as boolean', function (): void {
        $absence = Absence::factory()->create(['is_closed' => true]);

        expect($absence->is_closed)->toBeTrue();
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListAbsences::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Absence::factory(2)->create();

        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => array_keys(AbsenceExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Absence::factory(2)->create();

        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => ['agent', 'start_date', 'end_date']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});

describe('service filter', function (): void {
    /**
     * The service filter must be satisfied by an active contract, not by any contract:
     * an agent who left the service but is still employed elsewhere used to slip through
     * because the service and the active contract were matched independently.
     */
    it('excludes an absence whose agent only has a closed contract in the service', function (): void {
        $service = Service::factory()->create();

        $leaver = Employee::factory()->create();
        Contract::factory()->for($leaver)->create([
            'service_id' => $service->id,
            'is_closed' => true,
            'is_suspended' => false,
            'start_date' => now()->subYears(2),
            'end_date' => now()->subYear(),
        ]);
        Contract::factory()->for($leaver)->create([
            'service_id' => Service::factory()->create()->id,
            'is_closed' => false,
            'is_suspended' => false,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
        ]);

        $current = Employee::factory()->create();
        Contract::factory()->for($current)->create([
            'service_id' => $service->id,
            'is_closed' => false,
            'is_suspended' => false,
            'start_date' => now()->subYear(),
            'end_date' => now()->addYear(),
        ]);

        $leaverAbsence = Absence::factory()->for($leaver)->create(['is_closed' => false]);
        $currentAbsence = Absence::factory()->for($current)->create(['is_closed' => false]);

        Livewire::test(ListAbsences::class)
            ->loadTable()
            ->filterTable('service_id', $service->id)
            ->assertCanSeeTableRecords([$currentAbsence])
            ->assertCanNotSeeTableRecords([$leaverAbsence]);
    });
});
