<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Observers;

use AcMarche\GuichetHdv\Events\TicketCreated;
use AcMarche\GuichetHdv\Models\Ticket;

final class TicketObserver
{
    /**
     * Broadcast the new ticket so the "Tickets du jour" page refreshes itself,
     * whatever created it.
     */
    public function created(Ticket $ticket): void
    {
        TicketCreated::dispatch($ticket);
    }
}
