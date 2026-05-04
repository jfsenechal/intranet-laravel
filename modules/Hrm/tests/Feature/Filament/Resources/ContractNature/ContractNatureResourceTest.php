<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\CreateContractNature;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\EditContractNature;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\ListContractNatures;
use AcMarche\Hrm\Filament\Resources\ContractNatures\Pages\ViewContractNature;
use AcMarche\Hrm\Models\ContractNature;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListContractNatures::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateContractNature::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = ContractNature::factory()->create();

        Livewire::test(ViewContractNature::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = ContractNature::factory()->create();

        Livewire::test(EditContractNature::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a contract nature', function (): void {
        $record = ContractNature::factory()->create();
        $newData = ContractNature::factory()->make();

        Livewire::test(EditContractNature::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(ContractNature::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = ContractNature::factory()->make();

        Livewire::test(CreateContractNature::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 50 characters' => [['name' => Str::random(51)], ['name' => 'max']],
        '`description` is max 255 characters' => [['description' => Str::random(256)], ['description' => 'max']],
    ]);
});
