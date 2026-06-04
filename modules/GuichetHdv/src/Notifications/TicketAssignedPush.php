<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Notifications;

use AcMarche\GuichetHdv\Filament\Pages\TicketsOfTheDay;
use AcMarche\GuichetHdv\Models\Ticket;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

final class TicketAssignedPush extends Notification
{
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
            ->data(['url' => TicketsOfTheDay::getUrl()])
            ->options(['TTL' => 600]);
    }
}
