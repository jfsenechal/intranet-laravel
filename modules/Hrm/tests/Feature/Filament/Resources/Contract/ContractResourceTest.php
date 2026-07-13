<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\ContractExport;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\CreateContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\EditContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ListContracts;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListContracts::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateContract::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Contract::factory()->create();

        Livewire::test(ViewContract::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Contract::factory()->create();

        Livewire::test(EditContract::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'job_title' => $record->job_title,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a contract', function (): void {
        $record = Contract::factory()->create();

        Livewire::test(EditContract::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'job_title' => 'New Job Title',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Contract::class, [
            'id' => $record->id,
            'job_title' => 'New Job Title',
        ]);
    });

    it('persists the employee_id passed via the query string when creating', function (): void {
        $employee = Employee::factory()->create();
        $employer = Employer::factory()->create();

        Livewire::withQueryParams(['employee_id' => $employee->id])
            ->test(CreateContract::class)
            ->assertSchemaStateSet(['employee_id' => $employee->id])
            ->fillForm([
                'employer_id' => $employer->id,
                'job_title' => 'Agent technique',
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Contract::class, [
            'employee_id' => $employee->id,
            'employer_id' => $employer->id,
            'job_title' => 'Agent technique',
        ]);
    });

    it('can replicate a contract to another employee from the view page', function (): void {
        $record = Contract::factory()->create([
            'job_title' => 'Original Job Title',
            'is_closed' => true,
            'is_suspended' => true,
        ]);
        $targetEmployee = Employee::factory()->create();

        Livewire::test(ViewContract::class, [
            'record' => $record->id,
        ])
            ->callAction('replicate', ['employee_id' => $targetEmployee->id])
            ->assertHasNoActionErrors();

        expect(Contract::query()->where('job_title', 'Original Job Title')->count())->toBe(2);

        $replica = Contract::query()
            ->where('job_title', 'Original Job Title')
            ->where('id', '!=', $record->id)
            ->first();

        expect($replica->employee_id)->toBe($targetEmployee->id);
        expect($replica->is_closed)->toBeTrue();
        expect($replica->is_suspended)->toBeTrue();
    });
});

describe('form validation', function (): void {
    it('validates that employer_id is required on create', function (): void {
        Livewire::test(CreateContract::class)
            ->fillForm([
                'job_title' => 'Some Title',
            ])
            ->call('create')
            ->assertHasFormErrors(['employer_id' => 'required'])
            ->assertNotNotified();
    });
});

describe('model behavior', function (): void {
    it('casts is_closed as boolean', function (): void {
        $contract = Contract::factory()->create(['is_closed' => true]);

        expect($contract->is_closed)->toBeTrue();
    });

    it('active scope excludes closed contracts', function (): void {
        Contract::factory()->create(['is_closed' => true]);
        $active = Contract::factory()->create(['is_closed' => false, 'is_suspended' => false, 'end_date' => null]);

        $contracts = Contract::query()->active()->get();

        expect($contracts->pluck('id'))->toContain($active->id);
        expect($contracts->where('is_closed', true))->toBeEmpty();
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListContracts::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Contract::factory(2)->create();

        Livewire::test(ListContracts::class)
            ->callAction('export', data: ['columns' => array_keys(ContractExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Contract::factory(2)->create();

        Livewire::test(ListContracts::class)
            ->callAction('export', data: ['columns' => ['agent', 'employer', 'start_date']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListContracts::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
