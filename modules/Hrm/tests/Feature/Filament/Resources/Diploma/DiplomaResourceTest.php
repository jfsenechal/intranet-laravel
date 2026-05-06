<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\CreateDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\EditDiploma;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ListDiplomas;
use AcMarche\Hrm\Filament\Resources\Diplomas\Pages\ViewDiploma;
use AcMarche\Hrm\Models\Diploma;
use AcMarche\Hrm\Models\Employee;
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
        Livewire::test(ListDiplomas::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateDiploma::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Diploma::factory()->create();

        Livewire::test(ViewDiploma::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Diploma::factory()->create();

        Livewire::test(EditDiploma::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can update a diploma', function (): void {
        $record = Diploma::factory()->create();
        $newData = Diploma::factory()->make();

        Livewire::test(EditDiploma::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Diploma::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $employee = Employee::factory()->create();
        $newData = Diploma::factory()->make(['employee_id' => $employee->id]);

        Livewire::test(CreateDiploma::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 150 characters' => [['name' => Str::random(151)], ['name' => 'max']],
    ]);
});
