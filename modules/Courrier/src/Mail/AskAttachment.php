<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Mail;

use AcMarche\Courrier\Models\IncomingMail;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a user who may not download an attachment asks someone who can to
 * share it. The mail carries the courrier details and a link to its view; it
 * never includes the attachment itself.
 */
final class AskAttachment extends Mailable
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    public function __construct(
        public readonly IncomingMail $incomingMail,
        public readonly string $askerName,
        public readonly string $askerEmail,
        public readonly ?string $note = null,
    ) {
        $this->captureSenderAddress();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
            replyTo: $this->askerEmail,
            subject: '[Indicateur] Demande de pièce jointe',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'courrier::mail.ask-attachment',
            with: [
                'incomingMail' => $this->incomingMail,
                'askerName' => $this->askerName,
                'askerEmail' => $this->askerEmail,
                'note' => $this->note,
                'url' => route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $this->incomingMail->id]),
            ],
        );
    }
}
