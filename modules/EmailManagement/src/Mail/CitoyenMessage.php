<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class CitoyenMessage extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $body,
        public $subject = 'Information importante - Ville de Marche-en-Famenne',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('email-management.citoyen_message.from.address'),
                config('email-management.citoyen_message.from.name'),
            ),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'email-management::mail.citoyen',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
