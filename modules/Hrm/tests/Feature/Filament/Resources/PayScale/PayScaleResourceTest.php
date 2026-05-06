<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\PayScales\Pages\CreatePayScale;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\EditPayScale;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\ListPayScales;
use AcMarche\Hrm\Filament\Resources\PayScales\Pages\ViewPayScale;
use AcMarche\Hrm\Models\PayScale;
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
        Livewire::test(ListPayScales::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreatePayScale::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = PayScale::factory()->create();

        Livewire::test(ViewPayScale::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = PayScale::factory()->create();

        Livewire::test(EditPayScale::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a pay scale', function (): void {
        $newData = PayScale::factory()->make();

        Livewire::test(CreatePayScale::class)
            ->fillForm([
                'name' => $newData->name,
                'description' => $newData->description,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(PayScale::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a pay scale', function (): void {
        $record = PayScale::factory()->create();
        $newData = PayScale::factory()->make();

        Livewire::test(EditPayScale::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(PayScale::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = PayScale::factory()->make();

        Livewire::test(CreatePayScale::class)
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

describe('table', function (): void {
    it('can search by name', function (): void {
        $records = PayScale::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListPayScales::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });

    it('can sort by name', function (): void {
        $records = PayScale::factory(3)->create();

        Livewire::test(ListPayScales::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });
});
