<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ConseilNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, array{disk: string, path: string, name: string}>  $files
     */
    public function __construct(
        public readonly string $subjectLine,
        public readonly string $body,
        public readonly array $files = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'conseil::mail.notification',
            with: [
                'body' => $this->body,
                'subject' => $this->subjectLine,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return array_map(
            fn (array $file): Attachment => Attachment::fromStorageDisk($file['disk'], $file['path'])
                ->as($file['name']),
            $this->files,
        );
    }
}
