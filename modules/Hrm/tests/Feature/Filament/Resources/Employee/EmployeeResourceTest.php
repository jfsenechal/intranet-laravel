<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\CreateEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\EditEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ListEmployees;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Models\Employee;
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

        Pdf::assertViewIs('hrm::pdf.employee');
        Pdf::assertViewHas('employee');
        Pdf::assertViewHas('selectedRelations', []);
    });

    it('can trigger the export pdf action with selected relations', function (): void {
        Pdf::fake();
        $record = Employee::factory()->create();

        Livewire::test(ViewEmployee::class, ['record' => $record->id])
            ->callAction('exportPdf', data: ['relations' => ['contracts', 'absences']])
            ->assertHasNoActionErrors();

        Pdf::assertViewIs('hrm::pdf.employee');
        Pdf::assertViewHas('selectedRelations', ['contracts', 'absences']);
    });
});
