<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\CreateOffice;
use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\EditOffice;
use AcMarche\GuichetHdv\Filament\Resources\Office\Pages\ListOffice;
use AcMarche\GuichetHdv\Models\Office;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv'));
    auth()->user()->update(['is_administrator' => true]);

    if (! Route::getRoutes()->getByName('filament.guichet-hdv.resources.offices.index')) {
        Route::get('/guichet-hdv/offices', fn (): string => '')->name('filament.guichet-hdv.resources.offices.index');
        Route::get('/guichet-hdv/offices/create', fn (): string => '')->name('filament.guichet-hdv.resources.offices.create');
        Route::get('/guichet-hdv/offices/{record}/edit', fn (): string => '')->name('filament.guichet-hdv.resources.offices.edit');
    }
});

it('can render the index page', function (): void {
    livewire(ListOffice::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateOffice::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $office = Office::factory()->create();

    livewire(EditOffice::class, [
        'record' => $office->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $office->name,
            'service' => $office->service,
        ]);
});

it('has columns', function (string $column): void {
    livewire(ListOffice::class)
        ->assertTableColumnExists($column);
})->with(['name', 'service']);

it('can create an office', function (): void {
    $office = Office::factory()->make();

    livewire(CreateOffice::class)
        ->fillForm([
            'name' => $office->name,
            'service' => $office->service,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Office::class, [
        'name' => $office->name,
        'service' => $office->service,
    ]);
});

it('can update an office', function (): void {
    $office = Office::factory()->create();

    livewire(EditOffice::class, [
        'record' => $office->id,
    ])
        ->fillForm(['name' => 'Guichet mis à jour'])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Office::class, [
        'id' => $office->id,
        'name' => 'Guichet mis à jour',
    ]);
});

it('can delete an office', function (): void {
    $office = Office::factory()->create();

    livewire(EditOffice::class, [
        'record' => $office->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Office::class, ['id' => $office->id]);
});

it('can bulk delete offices', function (): void {
    $offices = Office::factory(3)->create();

    livewire(ListOffice::class)
        ->loadTable()
        ->assertCanSeeTableRecords($offices)
        ->selectTableRecords($offices)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($offices);

    $offices->each(fn (Office $office) => assertDatabaseMissing(Office::class, ['id' => $office->id]));
});

it('validates required fields', function (): void {
    livewire(CreateOffice::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required'])
        ->assertNotNotified();
});
