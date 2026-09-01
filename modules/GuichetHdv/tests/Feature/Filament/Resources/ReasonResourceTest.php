<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Enums\ServicesEnum;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\CreateReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\EditReason;
use AcMarche\GuichetHdv\Filament\Resources\Reason\Pages\ListReason;
use AcMarche\GuichetHdv\Models\Reason;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv-panel'));
    auth()->user()->update(['is_administrator' => true]);
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
            'service' => $reason->service,
        ]);
});

it('has columns', function (string $column): void {
    livewire(ListReason::class)
        ->assertTableColumnExists($column);
})->with(['id', 'content', 'service']);

it('can create a reason', function (): void {
    livewire(CreateReason::class)
        ->fillForm([
            'content' => 'Carte d\'identité (DEMANDE/RETRAIT)',
            'service' => ServicesEnum::POPULATION->value,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Reason::class, [
        'content' => 'Carte d\'identité (DEMANDE/RETRAIT)',
        'service' => ServicesEnum::POPULATION->value,
    ]);
});

it('can create a reason without a service', function (): void {
    livewire(CreateReason::class)
        ->fillForm([
            'content' => 'Retrait document',
            'service' => null,
        ])
        ->call('create')
        ->assertNotified()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Reason::class, [
        'content' => 'Retrait document',
        'service' => null,
    ]);
});

it('can update a reason', function (): void {
    $reason = Reason::factory()->create(['service' => ServicesEnum::POPULATION]);

    livewire(EditReason::class, [
        'record' => $reason->id,
    ])
        ->fillForm([
            'content' => 'Passeport (DEMANDE ou RETRAIT)',
            'service' => ServicesEnum::ETAT_CIVIL->value,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Reason::class, [
        'id' => $reason->id,
        'content' => 'Passeport (DEMANDE ou RETRAIT)',
        'service' => ServicesEnum::ETAT_CIVIL->value,
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
