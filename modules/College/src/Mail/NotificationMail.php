<?php

declare(strict_types=1);

namespace AcMarche\College\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

final class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path: string, name: string, mime: string}>  $files
     */
    public function __construct(
        public readonly string $sujet,
        public readonly string $body,
        public readonly Carbon $dateCollege,
        public readonly array $files,
        public readonly string $fromAddress,
        public readonly string $fromName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: $this->sujet,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'college::mail.notification',
            with: [
                'body' => $this->body,
                'dateCollege' => $this->dateCollege,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            fn (array $file): Attachment => Attachment::fromStorageDisk('local', $file['path'])
                ->as($file['name'])
                ->withMime($file['mime']),
            $this->files,
        );
    }
}
