<?php

namespace AcMarche\Courrier\Mail;

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class IncomingMailNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, IncomingMail>  $incomingMails
     */
    public function __construct(
        public readonly Recipient $recipient,
        public readonly Collection $incomingMails,
        public readonly bool $includeAttachments = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('app.name')),
            subject: 'Notification de courriers entrants',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'courrier::mail.incoming-mail-notification',
            with: [
                'recipient' => $this->recipient,
                'incomingMails' => $this->incomingMails,
                'url' => url('/indicateur'),
            ],
        );
    }

    /**
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        if (! $this->includeAttachments) {
            return [];
        }

        $attachments = [];
        $disk = config('courrier.storage.disk', 'public');
        $directory = config('courrier.storage.directory', 'courrier');

        foreach ($this->incomingMails as $incomingMail) {
            foreach ($incomingMail->attachments as $attachment) {
                $path = "{$directory}/{$attachment->file_name}";
                if (Storage::disk($disk)->exists($path)) {
                    $attachments[] = Attachment::fromStorageDisk($disk, $path)
                        ->as($attachment->file_name)
                        ->withMime($attachment->mime);
                }
            }
        }

        return $attachments;
    }
}
