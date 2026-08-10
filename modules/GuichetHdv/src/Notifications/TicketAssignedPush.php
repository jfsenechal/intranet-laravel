<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Notifications;

use AcMarche\GuichetHdv\Filament\Pages\TicketsOfTheDay;
use AcMarche\GuichetHdv\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class TicketAssignedPush extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Drop the job instead of failing it when the ticket is gone by the time
     * the worker picks it up — a push about a deleted ticket is pointless.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public readonly Ticket $ticket) {}

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Ticket assigné')
            ->body(sprintf(
                'Ticket #%s (%s) pris en charge au guichet %s.',
                $this->ticket->number,
                $this->ticket->service,
                $this->ticket->office?->name ?? '—',
            ))
            ->icon('/images/Marche_logo.png')
            ->badge('/images/Marche_logo.png')
            // The panel must be named explicitly: on a queue worker there is no
            // current panel, so Filament would fall back to the default one and
            // fail to find this page's route.
            ->data(['url' => TicketsOfTheDay::getUrl(panel: 'guichet-hdv-panel')])
            ->options(['TTL' => 600]);
    }
}
