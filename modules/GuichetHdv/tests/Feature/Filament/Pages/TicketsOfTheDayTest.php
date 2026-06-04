<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Enums\RolesEnum;
use AcMarche\GuichetHdv\Filament\Pages\TicketsOfTheDay;
use AcMarche\GuichetHdv\Models\Office;
use AcMarche\GuichetHdv\Models\Ticket;
use AcMarche\Security\Models\Role;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

function actingAsGuichetAgent(): void
{
    $role = Role::create(['name' => RolesEnum::ROLE_GUICHET_AGENT->value]);
    auth()->user()->roles()->attach($role);
    auth()->user()->unsetRelation('roles');
}

it('can render the page', function (): void {
    livewire(TicketsOfTheDay::class)
        ->assertOk();
});

it('has a header action to add a ticket', function (): void {
    livewire(TicketsOfTheDay::class)
        ->assertActionExists('createTicket');
});

it('hides agent actions from non-agents', function (): void {
    Ticket::factory()->create(['office_id' => null, 'archive' => false, 'createdAt' => now()]);

    livewire(TicketsOfTheDay::class)
        ->assertActionHidden('assignOffice')
        ->assertActionHidden('cancelTicket');
});

it('lets a guichet agent assign an office to a pending ticket', function (): void {
    actingAsGuichetAgent();
    $office = Office::factory()->create();
    $ticket = Ticket::factory()->create(['office_id' => null, 'archive' => false, 'createdAt' => now()]);
    $username = auth()->user()->username ?? auth()->user()->name;

    livewire(TicketsOfTheDay::class)
        ->callAction('assignOffice', data: ['office_id' => $office->id], arguments: ['ticket' => $ticket->id])
        ->assertHasNoActionErrors()
        ->assertNotified();

    $ticket->refresh();

    expect($ticket->office_id)->toBe($office->id)
        ->and($ticket->assigned_by)->toBe($username)
        ->and($ticket->assigned_date)->not->toBeNull()
        ->and($ticket->assigned_date->isToday())->toBeTrue();
});

it('lets a guichet agent cancel (archive) a pending ticket', function (): void {
    actingAsGuichetAgent();
    $ticket = Ticket::factory()->create(['office_id' => null, 'archive' => false, 'createdAt' => now()]);

    livewire(TicketsOfTheDay::class)
        ->callAction('cancelTicket', arguments: ['ticket' => $ticket->id])
        ->assertNotified();

    expect($ticket->refresh()->archive)->toBeTrue();
});

it('lets a guichet agent cancel (archive) a processing ticket', function (): void {
    actingAsGuichetAgent();
    $office = Office::factory()->create();
    $ticket = Ticket::factory()->create(['office_id' => $office->id, 'archive' => false, 'createdAt' => now()]);

    livewire(TicketsOfTheDay::class)
        ->callAction('cancelTicket', arguments: ['ticket' => $ticket->id])
        ->assertNotified();

    expect($ticket->refresh()->archive)->toBeTrue();
});

it('lists today pending tickets (no office, not archived)', function (): void {
    $pending = Ticket::factory()->create([
        'office_id' => null,
        'archive' => false,
        'createdAt' => now(),
    ]);

    livewire(TicketsOfTheDay::class)
        ->assertSee('#'.$pending->number);
});

it('lists today processing tickets (with office, not archived)', function (): void {
    $office = Office::factory()->create(['name' => 'Guichet Population']);
    $processing = Ticket::factory()->create([
        'office_id' => $office->id,
        'archive' => false,
        'createdAt' => now(),
    ]);

    livewire(TicketsOfTheDay::class)
        ->assertSee('#'.$processing->number)
        ->assertSee('Guichet Population');
});

it('separates pending from processing tickets', function (): void {
    $office = Office::factory()->create();
    $pending = Ticket::factory()->create(['office_id' => null, 'archive' => false, 'createdAt' => now()]);
    $processing = Ticket::factory()->create(['office_id' => $office->id, 'archive' => false, 'createdAt' => now()]);

    $page = livewire(TicketsOfTheDay::class);

    expect($page->instance()->getPendingTickets()->pluck('id'))->toContain($pending->id)
        ->not->toContain($processing->id);

    expect($page->instance()->getProcessingTickets()->pluck('id'))->toContain($processing->id)
        ->not->toContain($pending->id);
});

it('excludes archived tickets', function (): void {
    $office = Office::factory()->create();
    $archivedPending = Ticket::factory()->create(['office_id' => null, 'archive' => true, 'createdAt' => now()]);
    $archivedProcessing = Ticket::factory()->create(['office_id' => $office->id, 'archive' => true, 'createdAt' => now()]);

    $page = livewire(TicketsOfTheDay::class);

    expect($page->instance()->getPendingTickets()->pluck('id'))->not->toContain($archivedPending->id);
    expect($page->instance()->getProcessingTickets()->pluck('id'))->not->toContain($archivedProcessing->id);
});

it('excludes tickets from other days', function (): void {
    $office = Office::factory()->create();
    $yesterdayPending = Ticket::factory()->create(['office_id' => null, 'archive' => false, 'createdAt' => now()->subDay()]);
    $yesterdayProcessing = Ticket::factory()->create(['office_id' => $office->id, 'archive' => false, 'createdAt' => now()->subDay()]);

    $page = livewire(TicketsOfTheDay::class);

    expect($page->instance()->getPendingTickets()->pluck('id'))->not->toContain($yesterdayPending->id);
    expect($page->instance()->getProcessingTickets()->pluck('id'))->not->toContain($yesterdayProcessing->id);
});
