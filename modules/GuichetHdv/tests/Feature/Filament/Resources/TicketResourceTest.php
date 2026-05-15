<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\CreateTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\EditTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\ListTicket;
use AcMarche\GuichetHdv\Filament\Resources\Ticket\Pages\ViewTicket;
use AcMarche\GuichetHdv\Models\Office;
use AcMarche\GuichetHdv\Models\Ticket;
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

    if (! Route::getRoutes()->getByName('filament.guichet-hdv.resources.tickets.index')) {
        Route::get('/guichet-hdv/tickets', fn (): string => '')->name('filament.guichet-hdv.resources.tickets.index');
        Route::get('/guichet-hdv/tickets/create', fn (): string => '')->name('filament.guichet-hdv.resources.tickets.create');
        Route::get('/guichet-hdv/tickets/{record}/edit', fn (): string => '')->name('filament.guichet-hdv.resources.tickets.edit');
        Route::get('/guichet-hdv/tickets/{record}', fn (): string => '')->name('filament.guichet-hdv.resources.tickets.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListTicket::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateTicket::class)
        ->assertOk();
});

it('can render the view page', function (): void {
    $ticket = Ticket::factory()->create();

    livewire(ViewTicket::class, [
        'record' => $ticket->id,
    ])
        ->assertOk();
});

it('can render the edit page', function (): void {
    $ticket = Ticket::factory()->create();

    livewire(EditTicket::class, [
        'record' => $ticket->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'number' => $ticket->number,
            'reason' => $ticket->reason,
            'service' => $ticket->service,
        ]);
});

it('has columns', function (string $column): void {
    livewire(ListTicket::class)
        ->assertTableColumnExists($column);
})->with(['number', 'reason', 'service', 'user_add', 'archive']);

it('can create a ticket', function (): void {
    $office = Office::factory()->create();

    livewire(CreateTicket::class)
        ->fillForm([
            'number' => '42',
            'reason' => 'Carte d\'identité (DEMANDE/RETRAIT)',
            'service' => 'Population',
            'office_id' => $office->id,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Ticket::class, [
        'number' => '42',
        'service' => 'Population',
    ]);
});

it('can update a ticket', function (): void {
    $ticket = Ticket::factory()->create();

    livewire(EditTicket::class, [
        'record' => $ticket->id,
    ])
        ->fillForm(['service' => 'État civil'])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Ticket::class, [
        'id' => $ticket->id,
        'service' => 'État civil',
    ]);
});

it('can delete a ticket', function (): void {
    $ticket = Ticket::factory()->create();

    livewire(EditTicket::class, [
        'record' => $ticket->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Ticket::class, ['id' => $ticket->id]);
});

it('can bulk delete tickets', function (): void {
    $tickets = Ticket::factory(3)->create();

    livewire(ListTicket::class)
        ->loadTable()
        ->assertCanSeeTableRecords($tickets)
        ->selectTableRecords($tickets)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($tickets);

    $tickets->each(fn (Ticket $ticket) => assertDatabaseMissing(Ticket::class, ['id' => $ticket->id]));
});

it('validates required fields', function (): void {
    livewire(CreateTicket::class)
        ->fillForm([
            'number' => null,
            'reason' => null,
            'service' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'number' => 'required',
            'service' => 'required',
        ])
        ->assertNotNotified();
});

it('can filter tickets by archive status', function (): void {
    $active = Ticket::factory()->create(['archive' => false]);
    $archived = Ticket::factory()->create(['archive' => true]);

    livewire(ListTicket::class)
        ->loadTable()
        ->filterTable('archive', true)
        ->assertCanSeeTableRecords([$archived])
        ->assertCanNotSeeTableRecords([$active]);
});

it('can search tickets by number', function (): void {
    $ticket1 = Ticket::factory()->create(['number' => '100']);
    $ticket2 = Ticket::factory()->create(['number' => '200']);

    livewire(ListTicket::class)
        ->loadTable()
        ->searchTable('100')
        ->assertCanSeeTableRecords([$ticket1])
        ->assertCanNotSeeTableRecords([$ticket2]);
});
