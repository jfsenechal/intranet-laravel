<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Rates\Pages\CreateRate;
use AcMarche\Mileage\Filament\Resources\Rates\Pages\EditRate;
use AcMarche\Mileage\Filament\Resources\Rates\Pages\ListRates;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));
    $this->user = User::factory()->create(['is_administrator' => true]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => $this->user->username]);
    $this->actingAs($this->user);
});

it('can render the index page', function (): void {
    livewire(ListRates::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateRate::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $rate = Rate::factory()->create();

    livewire(EditRate::class, ['record' => $rate->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'amount' => $rate->amount,
            'omnium' => $rate->omnium,
            'start_date' => $rate->start_date->format('Y-m-d'),
            'end_date' => $rate->end_date->format('Y-m-d'),
        ]);
});

it('can list rates', function (): void {
    $rates = Rate::factory(3)->create();

    livewire(ListRates::class)
        ->loadTable()
        ->assertCanSeeTableRecords($rates);
});

it('has table columns', function (string $column): void {
    livewire(ListRates::class)
        ->assertTableColumnExists($column);
})->with(['start_date', 'end_date', 'amount', 'omnium']);

it('can sort column', function (string $column): void {
    $rates = Rate::factory(5)->create();

    livewire(ListRates::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($rates->sortBy($column)->values(), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($rates->sortByDesc($column)->values(), inOrder: true);
})->with(['start_date', 'amount']);

it('can create a rate', function (): void {
    $rate = Rate::factory()->make();

    livewire(CreateRate::class)
        ->fillForm([
            'amount' => $rate->amount,
            'omnium' => $rate->omnium,
            'start_date' => $rate->start_date->format('Y-m-d'),
            'end_date' => $rate->end_date->format('Y-m-d'),
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Rate::class, [
        'amount' => $rate->amount,
        'omnium' => $rate->omnium,
    ]);
});

it('can update a rate', function (): void {
    $rate = Rate::factory()->create();
    $newData = Rate::factory()->make();

    livewire(EditRate::class, ['record' => $rate->id])
        ->fillForm([
            'amount' => $newData->amount,
            'omnium' => $newData->omnium,
            'start_date' => $newData->start_date->format('Y-m-d'),
            'end_date' => $newData->end_date->format('Y-m-d'),
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Rate::class, [
        'id' => $rate->id,
        'amount' => $newData->amount,
        'omnium' => $newData->omnium,
    ]);
});

it('validates the form data', function (array $data, array $errors): void {
    $rate = Rate::factory()->create();
    $newData = Rate::factory()->make();

    livewire(EditRate::class, ['record' => $rate->id])
        ->fillForm([
            'amount' => $newData->amount,
            'omnium' => $newData->omnium,
            'start_date' => $newData->start_date,
            'end_date' => $newData->end_date,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`amount` is required' => [['amount' => null], ['amount' => 'required']],
    '`omnium` is required' => [['omnium' => null], ['omnium' => 'required']],
    '`start_date` is required' => [['start_date' => null], ['start_date' => 'required']],
    '`end_date` is required' => [['end_date' => null], ['end_date' => 'required']],
]);
