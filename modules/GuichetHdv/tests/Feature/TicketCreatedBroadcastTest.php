<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Events\TicketCreated;
use AcMarche\GuichetHdv\Filament\Pages\TicketsOfTheDay;
use AcMarche\GuichetHdv\Models\Ticket;
use Filament\Facades\Filament;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Event;

use function Pest\Livewire\livewire;

it('broadcasts TicketCreated when a ticket is created', function (): void {
    Event::fake([TicketCreated::class]);

    $ticket = Ticket::factory()->create();

    Event::assertDispatched(
        TicketCreated::class,
        fn (TicketCreated $event): bool => $event->ticket->is($ticket),
    );
});

it('does not broadcast TicketCreated when a ticket is updated', function (): void {
    $ticket = Ticket::factory()->create();

    Event::fake([TicketCreated::class]);

    $ticket->update(['reason' => 'Autre motif']);

    Event::assertNotDispatched(TicketCreated::class);
});

it('broadcasts on the tickets channel the page listens to', function (): void {
    $event = new TicketCreated(Ticket::factory()->create());

    expect($event)->toBeInstanceOf(ShouldBroadcastNow::class)
        ->and($event->broadcastAs())->toBe('ticket.created')
        ->and($event->broadcastOn())->toEqual([new PrivateChannel('guichet-hdv.tickets')])
        ->and($event->broadcastWith())->toMatchArray([
            'id' => $event->ticket->id,
            'number' => $event->ticket->number,
            'service' => $event->ticket->service,
        ]);
});

it('listens for the created broadcast on the tickets of the day page', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv-panel'));
    auth()->user()->update(['is_administrator' => true]);

    livewire(TicketsOfTheDay::class)
        ->assertSeeHtml('ticket.created');
});
