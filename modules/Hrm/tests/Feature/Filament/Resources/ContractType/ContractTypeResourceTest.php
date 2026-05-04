<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\CreateContractType;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\EditContractType;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\ListContractTypes;
use AcMarche\Hrm\Filament\Resources\ContractTypes\Pages\ViewContractType;
use AcMarche\Hrm\Models\ContractType;
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
        Livewire::test(ListContractTypes::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateContractType::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = ContractType::factory()->create();

        Livewire::test(ViewContractType::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = ContractType::factory()->create();

        Livewire::test(EditContractType::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a contract type', function (): void {
        $record = ContractType::factory()->create();
        $newData = ContractType::factory()->make();

        Livewire::test(EditContractType::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(ContractType::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = ContractType::factory()->make();

        Livewire::test(CreateContractType::class)
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
