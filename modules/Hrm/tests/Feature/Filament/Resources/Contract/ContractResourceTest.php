<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\ContractExport;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\CreateContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\EditContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ListContracts;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ContractsRelationManager;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\ContractNature;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\PayScale;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
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

    it('shows the nature and pay scale columns', function (): void {
        $nature = ContractNature::factory()->create();
        $payScale = PayScale::factory()->create();
        $record = Contract::factory()->create([
            'contract_nature_id' => $nature->id,
            'pay_scale_id' => $payScale->id,
        ]);

        Livewire::test(ListContracts::class)
            ->assertTableColumnStateSet('contractNature.name', $nature->name, $record)
            ->assertTableColumnStateSet('payScale.name', $payScale->name, $record);
    });

    it('shows the nature and pay scale columns in the employee relation manager', function (): void {
        $employee = Employee::factory()->create();
        $nature = ContractNature::factory()->create();
        $payScale = PayScale::factory()->create();
        $record = Contract::factory()->create([
            'employee_id' => $employee->id,
            'contract_nature_id' => $nature->id,
            'pay_scale_id' => $payScale->id,
        ]);

        Livewire::test(ContractsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertTableColumnStateSet('contractNature.name', $nature->name, $record)
            ->assertTableColumnStateSet('payScale.name', $payScale->name, $record);
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

    it('does not preselect an agent when opening the replicate modal', function (): void {
        $record = Contract::factory()->create();

        Livewire::test(ViewContract::class, [
            'record' => $record->id,
        ])
            ->mountAction('replicate')
            ->assertSchemaStateSet(['employee_id' => null]);
    });

    it('requires an agent when replicating a contract', function (): void {
        $record = Contract::factory()->create(['job_title' => 'Unreplicated Job Title']);

        Livewire::test(ViewContract::class, [
            'record' => $record->id,
        ])
            ->callAction('replicate', ['employee_id' => null])
            ->assertHasActionErrors(['employee_id' => 'required']);

        expect(Contract::query()->where('job_title', 'Unreplicated Job Title')->count())->toBe(1);
    });

    it('can replicate a contract to another employee from the employee relation manager', function (): void {
        $employee = Employee::factory()->create();
        $record = Contract::factory()->create([
            'employee_id' => $employee->id,
            'job_title' => 'Relation Job Title',
            'is_closed' => false,
        ]);
        $targetEmployee = Employee::factory()->create();

        Livewire::test(ContractsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->callAction(TestAction::make('replicate')->table($record), ['employee_id' => $targetEmployee->id])
            ->assertHasNoActionErrors();

        $replica = Contract::query()
            ->where('job_title', 'Relation Job Title')
            ->where('id', '!=', $record->id)
            ->first();

        expect($replica)->not->toBeNull();
        expect($replica->employee_id)->toBe($targetEmployee->id);
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

    it('casts is_replacement as boolean', function (): void {
        $replacement = Contract::factory()->create(['is_replacement' => true]);
        $regular = Contract::factory()->create(['is_replacement' => false]);

        expect($replacement->fresh()->is_replacement)->toBeTrue()
            ->and($regular->fresh()->is_replacement)->toBeFalse();
    });

    it('active scope excludes closed contracts', function (): void {
        Contract::factory()->create(['is_closed' => true]);
        $active = Contract::factory()->create(['is_closed' => false, 'is_suspended' => false, 'end_date' => null]);

        $contracts = Contract::query()->active()->get();

        expect($contracts->pluck('id'))->toContain($active->id);
        expect($contracts->where('is_closed', true))->toBeEmpty();
    });

    it('active scope excludes contracts that have not started yet', function (): void {
        $future = Contract::factory()->create([
            'is_closed' => false,
            'is_suspended' => false,
            'start_date' => now()->addDay(),
            'end_date' => null,
        ]);

        expect(Contract::query()->active()->pluck('id'))->not->toContain($future->id);
    });

    it('active scope keeps contracts starting today and contracts without a start date', function (): void {
        $startsToday = Contract::factory()->create([
            'is_closed' => false,
            'is_suspended' => false,
            'start_date' => now(),
            'end_date' => null,
        ]);

        $withoutStartDate = Contract::factory()->create([
            'is_closed' => false,
            'is_suspended' => false,
            'start_date' => null,
            'end_date' => null,
        ]);

        expect(Contract::query()->active()->pluck('id'))
            ->toContain($startsToday->id)
            ->toContain($withoutStartDate->id);
    });
});

describe('nature filter', function (): void {
    it('filters contracts by multiple selected natures', function (): void {
        $natureA = ContractNature::factory()->create();
        $natureB = ContractNature::factory()->create();
        $natureC = ContractNature::factory()->create();

        $contractA = Contract::factory()->create(['contract_nature_id' => $natureA->id]);
        $contractB = Contract::factory()->create(['contract_nature_id' => $natureB->id]);
        $contractC = Contract::factory()->create(['contract_nature_id' => $natureC->id]);

        Livewire::test(ListContracts::class)
            ->loadTable()
            ->filterTable('contract_nature_id', [$natureA->id, $natureB->id])
            ->assertCanSeeTableRecords([$contractA, $contractB])
            ->assertCanNotSeeTableRecords([$contractC]);
    });
});

describe('replaces column and filter', function (): void {
    it('shows the replaced agent in the table', function (): void {
        $replaced = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $contract = Contract::factory()->create(['replaces_id' => $replaced->id]);
        $other = Contract::factory()->create(['replaces_id' => null]);

        Livewire::test(ListContracts::class)
            ->loadTable()
            ->assertTableColumnStateSet('replaces.last_name', 'Dupont Marie', $contract)
            ->assertTableColumnStateSet('replaces.last_name', null, $other);
    });

    it('only offers agents that are actually replaced as filter options', function (): void {
        $replaced = Employee::factory()->create();
        Contract::factory()->create(['replaces_id' => $replaced->id]);

        // Never replaced and without a contract, so the name can only come from
        // the filter options.
        Employee::factory()->create(['last_name' => 'Jamaisremplace', 'first_name' => 'Bob']);

        $html = Livewire::test(ListContracts::class)
            ->loadTable()
            ->html();

        expect($html)->toContain('value="'.$replaced->id.'"')
            ->not->toContain('Jamaisremplace');
    });

    it('shows the replaced agent in the contracts relation manager', function (): void {
        $employee = Employee::factory()->create();
        $replaced = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $contract = Contract::factory()->create([
            'employee_id' => $employee->id,
            'replaces_id' => $replaced->id,
            'is_closed' => false,
        ]);

        Livewire::test(ContractsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->loadTable()
            ->assertTableColumnStateSet('replaces.last_name', 'Dupont Marie', $contract);
    });

    it('filters contracts by the replaced agent', function (): void {
        $replaced = Employee::factory()->create();
        $otherReplaced = Employee::factory()->create();

        $matching = Contract::factory()->create(['replaces_id' => $replaced->id]);
        $nonMatching = Contract::factory()->create(['replaces_id' => $otherReplaced->id]);
        $withoutReplacement = Contract::factory()->create(['replaces_id' => null]);

        Livewire::test(ListContracts::class)
            ->loadTable()
            ->filterTable('replaces', $replaced->id)
            ->assertCanSeeTableRecords([$matching])
            ->assertCanNotSeeTableRecords([$nonMatching, $withoutReplacement]);
    });
});

describe('replacement section', function (): void {
    it('shows the replaced agent on the view page of a replacement contract', function (): void {
        $replaced = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $contract = Contract::factory()->create([
            'is_replacement' => true,
            'replaces_id' => $replaced->id,
        ]);

        Livewire::test(ViewContract::class, ['record' => $contract->id])
            ->assertOk()
            ->assertSee('Remplacement')
            ->assertSee('Dupont Marie');
    });

    it('hides the section when the contract is not a replacement', function (): void {
        $replaced = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $contract = Contract::factory()->create([
            'is_replacement' => false,
            'replaces_id' => $replaced->id,
        ]);

        Livewire::test(ViewContract::class, ['record' => $contract->id])
            ->assertOk()
            ->assertDontSee('Remplacement')
            ->assertDontSee('Dupont Marie');
    });

    it('links the replaced agent to their view page', function (): void {
        $replaced = Employee::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Marie']);
        $contract = Contract::factory()->create([
            'is_replacement' => true,
            'replaces_id' => $replaced->id,
        ]);

        Livewire::test(ViewContract::class, ['record' => $contract->id])
            ->assertOk()
            ->assertSee(ViewEmployee::getUrl(['record' => $replaced], panel: 'hrm-panel'), escape: false);
    });

    it('does not display the is_replacement flag itself', function (): void {
        $contract = Contract::factory()->create(['is_replacement' => true]);

        Livewire::test(ViewContract::class, ['record' => $contract->id])
            ->assertOk()
            ->assertSchemaComponentDoesNotExist('is_replacement');
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
