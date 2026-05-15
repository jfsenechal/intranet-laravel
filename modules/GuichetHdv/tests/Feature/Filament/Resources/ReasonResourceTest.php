<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\CreateReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\EditReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\ListReason;
use AcMarche\GuichetHdv\Models\Reason;
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

    if (! Route::getRoutes()->getByName('filament.guichet-hdv.resources.reasons.index')) {
        Route::get('/guichet-hdv/reasons', fn (): string => '')->name('filament.guichet-hdv.resources.reasons.index');
        Route::get('/guichet-hdv/reasons/create', fn (): string => '')->name('filament.guichet-hdv.resources.reasons.create');
        Route::get('/guichet-hdv/reasons/{record}/edit', fn (): string => '')->name('filament.guichet-hdv.resources.reasons.edit');
    }
});

it('can render the index page', function (): void {
    livewire(ListReason::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateReason::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $reason = Reason::factory()->create();

    livewire(EditReason::class, [
        'record' => $reason->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'content' => $reason->content,
        ]);
});

it('has columns', function (string $column): void {
    livewire(ListReason::class)
        ->assertTableColumnExists($column);
})->with(['id', 'content']);

it('can create a reason', function (): void {
    livewire(CreateReason::class)
        ->fillForm(['content' => 'Carte d\'identité (DEMANDE/RETRAIT)'])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Reason::class, [
        'content' => 'Carte d\'identité (DEMANDE/RETRAIT)',
    ]);
});

it('can update a reason', function (): void {
    $reason = Reason::factory()->create();

    livewire(EditReason::class, [
        'record' => $reason->id,
    ])
        ->fillForm(['content' => 'Passeport (DEMANDE ou RETRAIT)'])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Reason::class, [
        'id' => $reason->id,
        'content' => 'Passeport (DEMANDE ou RETRAIT)',
    ]);
});

it('can delete a reason', function (): void {
    $reason = Reason::factory()->create();

    livewire(EditReason::class, [
        'record' => $reason->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Reason::class, ['id' => $reason->id]);
});

it('can bulk delete reasons', function (): void {
    $reasons = Reason::factory(3)->create();

    livewire(ListReason::class)
        ->loadTable()
        ->assertCanSeeTableRecords($reasons)
        ->selectTableRecords($reasons)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($reasons);

    $reasons->each(fn (Reason $reason) => assertDatabaseMissing(Reason::class, ['id' => $reason->id]));
});

it('validates required fields', function (): void {
    livewire(CreateReason::class)
        ->fillForm(['content' => null])
        ->call('create')
        ->assertHasFormErrors(['content' => 'required'])
        ->assertNotNotified();
});
