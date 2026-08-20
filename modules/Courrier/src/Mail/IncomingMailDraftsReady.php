<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Mail;

use AcMarche\Courrier\Filament\Pages\DraftIncomingMails;
use AcMarche\Courrier\Models\IncomingMail;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

use function sprintf;

/**
 * Sent to the administrator once the AI has turned their Inbox selection into
 * draft courriers.
 *
 * The mail is the way back into the queue: nothing else lists the drafts, since
 * they are kept out of the day's listing until they are validated. The link
 * opens the first one, and validating it walks to the next.
 */
final class IncomingMailDraftsReady extends Mailable
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    /**
     * @param  Collection<int, IncomingMail>  $drafts
     * @param  list<string>  $failures  file names the analysis could not read; those messages are
     *                                  still in the mailbox and have to be encoded by hand
     */
    public function __construct(
        public readonly Collection $drafts,
        public readonly array $failures = [],
    ) {
        $this->captureSenderAddress();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
            subject: sprintf('[Indicateur] %d brouillon(s) de courrier à vérifier', $this->drafts->count()),
        );
    }

    public function content(): Content
    {
        $first = $this->drafts->first();

        return new Content(
            html: 'courrier::mail.incoming-mail-drafts-ready',
            with: [
                'drafts' => $this->drafts,
                'failures' => $this->failures,
                // Straight into the first draft of this batch, so the link
                // starts the walk rather than landing on another listing.
                'url' => $first instanceof IncomingMail
                    ? route('filament.courrier-panel.resources.incoming-mails.edit', ['record' => $first->id])
                    : null,
                'draftsUrl' => DraftIncomingMails::getUrl(panel: 'courrier-panel'),
            ],
        );
    }
}
