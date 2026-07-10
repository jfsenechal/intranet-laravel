<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Exports\EmployeeExport;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\CreateEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\EditEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ListEmployees;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Prerequisite;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $hrmAdminRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->adminUser->roles()->attach($hrmAdminRole);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListEmployees::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateEmployee::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(EditEmployee::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'last_name' => $record->last_name,
                'first_name' => $record->first_name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create an employee', function (): void {
        Livewire::test(CreateEmployee::class)
            ->fillForm([
                'last_name' => 'Doe',
                'first_name' => 'John',
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Employee::class, [
            'last_name' => 'Doe',
            'first_name' => 'John',
        ]);
    });

    it('can update an employee', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(EditEmployee::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'last_name' => 'NewLastName',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Employee::class, [
            'id' => $record->id,
            'last_name' => 'NewLastName',
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        Livewire::test(CreateEmployee::class)
            ->fillForm([
                'last_name' => 'Doe',
                'first_name' => 'John',
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`last_name` is required' => [['last_name' => null], ['last_name' => 'required']],
        '`first_name` is required' => [['first_name' => null], ['first_name' => 'required']],
        '`last_name` is max 100 characters' => [['last_name' => Str::random(101)], ['last_name' => 'max']],
        '`first_name` is max 100 characters' => [['first_name' => Str::random(101)], ['first_name' => 'max']],
    ]);
});

describe('model behavior', function (): void {
    it('automatically generates a uuid on creation', function (): void {
        $employee = Employee::factory()->create(['uuid' => null]);

        expect($employee->uuid)
            ->not->toBeNull()
            ->toBeString();
    });

    it('casts status to StatusEnum', function (): void {
        $employee = Employee::factory()->create(['status' => StatusEnum::AGENT->value]);

        expect($employee->status)->toBe(StatusEnum::AGENT);
    });

    it('casts is_archived as boolean', function (): void {
        $employee = Employee::factory()->create(['is_archived' => true]);

        expect($employee->is_archived)->toBeTrue();
    });
});

describe('table', function (): void {
    it('shows employees in the listing', function (): void {
        $records = Employee::factory(3)->create();

        Livewire::test(ListEmployees::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records);
    });

    it('filters employees by employer through their contracts', function (): void {
        $employer = AcMarche\Hrm\Models\Employer::factory()->create(['parent_id' => null]);
        $otherEmployer = AcMarche\Hrm\Models\Employer::factory()->create(['parent_id' => null]);

        $matching = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $matching->id,
            'employer_id' => $employer->id,
        ]);

        $nonMatching = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $nonMatching->id,
            'employer_id' => $otherEmployer->id,
        ]);

        Livewire::test(ListEmployees::class)
            ->loadTable()
            ->filterTable('employer_id', $employer->id)
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$nonMatching]);
    });
});

describe('table scoping by role', function (): void {
    it('shows a direction head only the employees of their direction', function (): void {
        $directionRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $director = User::factory()->create(['is_administrator' => false, 'username' => 'director1']);
        $director->roles()->attach($directionRole);

        $direction = AcMarche\Hrm\Models\Direction::factory()->create(['director' => 'director1']);
        $otherDirection = AcMarche\Hrm\Models\Direction::factory()->create(['director' => 'someone-else']);

        $visible = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $visible->id,
            'direction_id' => $direction->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        $hidden = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $hidden->id,
            'direction_id' => $otherDirection->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        $this->actingAs($director);

        Livewire::test(ListEmployees::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$visible])
            ->assertCanNotSeeTableRecords([$hidden]);
    });

    it('shows no employees to a direction head without a direction', function (): void {
        $directionRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_DIRECTION->value]);
        $director = User::factory()->create(['is_administrator' => false, 'username' => 'orphan']);
        $director->roles()->attach($directionRole);

        $employee = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $employee->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        $this->actingAs($director);

        Livewire::test(ListEmployees::class)
            ->loadTable()
            ->assertCanNotSeeTableRecords([$employee]);
    });

    it('shows a ville reader only employees under the ville employer tree', function (): void {
        $readRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_VILLE_READ->value]);
        $reader = User::factory()->create(['is_administrator' => false, 'username' => 'villereader']);
        $reader->roles()->attach($readRole);

        $ville = AcMarche\Hrm\Models\Employer::factory()->create(['slug' => 'ville', 'parent_id' => null]);
        $villeChild = AcMarche\Hrm\Models\Employer::factory()->create(['parent_id' => $ville->id]);
        $cpas = AcMarche\Hrm\Models\Employer::factory()->create(['slug' => 'cpas', 'parent_id' => null]);

        $visible = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $visible->id,
            'employer_id' => $villeChild->id,
        ]);

        $hidden = Employee::factory()->create();
        AcMarche\Hrm\Models\Contract::factory()->create([
            'employee_id' => $hidden->id,
            'employer_id' => $cpas->id,
        ]);

        $this->actingAs($reader);

        Livewire::test(ListEmployees::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$visible])
            ->assertCanNotSeeTableRecords([$hidden]);
    });
});

describe('emploi tab', function (): void {
    it('displays the prerequisite details inline on the view page', function (): void {
        $prerequisite = Prerequisite::factory()->create([
            'name' => 'Prérequis A1',
            'profession' => 'Ingénieur civil',
        ]);
        $record = Employee::factory()->create(['prerequisite_id' => $prerequisite->id]);

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->assertOk()
            ->assertSee('Prérequis A1')
            ->assertSee('Ingénieur civil');
    });
});

describe('export pdf action', function (): void {
    it('renders the export pdf action on the view page', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->assertActionExists('exportPdf');
    });

    it('can trigger the export pdf action without relations', function (): void {
        Pdf::fake();
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->callAction('exportPdf', data: ['relations' => []])
            ->assertHasNoActionErrors();

        Pdf::assertRespondedWithPdf(fn (Spatie\LaravelPdf\PdfBuilder $pdf): bool => $pdf->viewName === 'hrm::pdf.employee'
            && array_key_exists('employee', $pdf->viewData)
            && ($pdf->viewData['selectedRelations'] ?? null) === []);
    });

    it('can trigger the export pdf action with selected relations', function (): void {
        Pdf::fake();
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->callAction('exportPdf', data: ['relations' => ['contracts', 'absences']])
            ->assertHasNoActionErrors();

        Pdf::assertRespondedWithPdf(fn (Spatie\LaravelPdf\PdfBuilder $pdf): bool => $pdf->viewName === 'hrm::pdf.employee'
            && ($pdf->viewData['selectedRelations'] ?? null) === ['contracts', 'absences']);
    });
});

describe('header actions authorization', function (): void {
    it('shows the header actions to an hrm administrator', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->assertActionExists('edit')
            ->assertActionExists('delete');
    });

    it('hides the header actions from a non-administrator hrm user', function (): void {
        $readRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_VILLE_READ->value]);
        $user = User::factory()->create(['is_administrator' => false, 'username' => 'jdoe']);
        $user->roles()->attach($readRole);
        $record = Employee::factory()->create(['username' => 'jdoe']);
        $this->actingAs($user);

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->assertOk()
            ->assertActionDoesNotExist('edit')
            ->assertActionDoesNotExist('delete');
    });
});

describe('create actions with relationship selects', function (): void {
    it('can mount the add evaluation action modal', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->mountAction('addEvaluation')
            ->assertActionMounted('addEvaluation')
            ->assertHasNoActionErrors();
    });

    it('can create an evaluation for the employee', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->callAction('addEvaluation', [
                'result' => AcMarche\Hrm\Enums\EvaluationResultEnum::cases()[0]->value,
                'evaluation_date' => now()->toDateString(),
            ])
            ->assertHasNoActionErrors();

        assertDatabaseHas(AcMarche\Hrm\Models\Evaluation::class, [
            'employee_id' => $record->id,
        ]);
    });

    it('can mount the add internship action modal', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->mountAction('addInternship')
            ->assertActionMounted('addInternship')
            ->assertHasNoActionErrors();
    });

    it('can mount the add application action modal', function (): void {
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->mountAction('addApplication')
            ->assertActionMounted('addApplication')
            ->assertHasNoActionErrors();
    });
});

describe('export csv action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListEmployees::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Employee::factory(2)->create();

        Livewire::test(ListEmployees::class)
            ->callAction('export', data: ['columns' => array_keys(EmployeeExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Employee::factory(2)->create();

        Livewire::test(ListEmployees::class)
            ->callAction('export', data: ['columns' => ['last_name', 'first_name', 'private_email']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListEmployees::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
