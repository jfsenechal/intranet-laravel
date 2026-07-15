<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Mail;

use AcMarche\Courrier\Models\IncomingMail;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Sent when a user who may download an attachment shares the courrier with a
 * recipient. The mail carries the courrier details and its attachment(s).
 */
final class ShareIncomingMail extends Mailable
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    public function __construct(
        public readonly IncomingMail $incomingMail,
        public readonly ?string $note = null,
    ) {
        $this->captureSenderAddress();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
            replyTo: config('mail.noreply_email'),
            subject: '[Indicateur] Partage de courrier',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'courrier::mail.share-incoming-mail',
            with: [
                'incomingMail' => $this->incomingMail,
                'note' => $this->note,
                'url' => route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $this->incomingMail->id]),
            ],
        );
    }

    /**
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        $disk = config('courrier.storage.disk', 'public');

        foreach ($this->incomingMail->attachments as $attachment) {
            if ($attachment->path !== null && Storage::disk($disk)->exists($attachment->path)) {
                $attachments[] = Attachment::fromStorageDisk($disk, $attachment->path)
                    ->as($attachment->file_name)
                    ->withMime($attachment->mime);
            }
        }

        return $attachments;
    }
}
