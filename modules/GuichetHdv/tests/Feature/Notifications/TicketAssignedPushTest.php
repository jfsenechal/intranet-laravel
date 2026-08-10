<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Models\Office;
use AcMarche\GuichetHdv\Models\Ticket;
use AcMarche\GuichetHdv\Notifications\TicketAssignedPush;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Queue;
use NotificationChannels\WebPush\WebPushMessage;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv-panel'));
});

it('is queued so a slow push service cannot stall the assign action', function (): void {
    expect(new TicketAssignedPush(Ticket::factory()->create()))
        ->toBeInstanceOf(ShouldQueue::class);
});

it('is pushed onto the queue instead of being delivered inline', function (): void {
    Queue::fake();

    NotificationFacade::send(
        User::factory()->create(),
        new TicketAssignedPush(Ticket::factory()->create()),
    );

    Queue::assertPushed(SendQueuedNotifications::class);
});

it('is dropped rather than failed when the ticket no longer exists', function (): void {
    expect((new TicketAssignedPush(Ticket::factory()->create()))->deleteWhenMissingModels)
        ->toBeTrue();
});

it('survives the serialization round-trip the queue puts it through', function (): void {
    $office = Office::factory()->create(['name' => 'Guichet 3']);
    $ticket = Ticket::factory()->create(['office_id' => $office->id]);
    $ticket->load('office');

    /** @var TicketAssignedPush $restored */
    $restored = unserialize(serialize(new TicketAssignedPush($ticket)));

    expect($restored->ticket->id)->toBe($ticket->id)
        ->and($restored->ticket->office?->name)->toBe('Guichet 3');
});

it('builds the payload when no panel is current, as on a queue worker', function (): void {
    $office = Office::factory()->create(['name' => 'Guichet 3']);
    $ticket = Ticket::factory()->create(['office_id' => $office->id]);
    $ticket->load('office');

    // A worker has no current panel; Filament would otherwise fall back to the
    // default panel, where this page has no route.
    Filament::setCurrentPanel(null);

    $message = (new TicketAssignedPush($ticket))->toWebPush(User::factory()->create());

    expect($message)->toBeInstanceOf(WebPushMessage::class)
        ->and($message->toArray()['data']['url'])->toContain('/guichet-hdv/tickets-of-the-day')
        ->and($message->toArray()['body'])->toContain('Guichet 3');
});
