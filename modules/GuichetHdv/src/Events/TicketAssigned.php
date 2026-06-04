<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Events;

use AcMarche\GuichetHdv\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketAssigned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Ticket $ticket) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('guichet-hdv.tickets'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ticket.assigned';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ticket->id,
            'number' => $this->ticket->number,
            'service' => $this->ticket->service,
            'office' => $this->ticket->office?->name,
        ];
    }
}
