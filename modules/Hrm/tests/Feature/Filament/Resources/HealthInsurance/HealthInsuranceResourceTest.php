<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\CreateHealthInsurance;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\EditHealthInsurance;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\ListHealthInsurances;
use AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages\ViewHealthInsurance;
use AcMarche\Hrm\Models\HealthInsurance;
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
        Livewire::test(ListHealthInsurances::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateHealthInsurance::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = HealthInsurance::factory()->create();

        Livewire::test(ViewHealthInsurance::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = HealthInsurance::factory()->create();

        Livewire::test(EditHealthInsurance::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a health insurance', function (): void {
        $newData = HealthInsurance::factory()->make();

        Livewire::test(CreateHealthInsurance::class)
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(HealthInsurance::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a health insurance', function (): void {
        $record = HealthInsurance::factory()->create();
        $newData = HealthInsurance::factory()->make();

        Livewire::test(EditHealthInsurance::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(HealthInsurance::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = HealthInsurance::factory()->make();

        Livewire::test(CreateHealthInsurance::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 100 characters' => [['name' => Str::random(101)], ['name' => 'max']],
    ]);
});
