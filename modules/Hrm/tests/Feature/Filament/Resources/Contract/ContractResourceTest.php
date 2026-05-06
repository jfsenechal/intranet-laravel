<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Contracts\Pages\CreateContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\EditContract;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ListContracts;
use AcMarche\Hrm\Filament\Resources\Contracts\Pages\ViewContract;
use AcMarche\Hrm\Models\Contract;
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
