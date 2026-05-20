<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Mail;

use AcMarche\AldermenAgenda\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class EventEmail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Event $event,
        public readonly bool $isPreview = false,
    ) {
        $prefix = $this->isPreview ? '[Aperçu] ' : '';
        $this->subject = $prefix.$this->event->title;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('app.name')),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->logo = public_path('images/Marche_logo.png');
        if (! file_exists($this->logo)) {
            $this->logo = null;
        }

        return new Content(
            html: 'aldermen-agenda::mail.event',
            with: [
                'event' => $this->event,
                'isPreview' => $this->isPreview,
                'logo' => $this->logo,
            ],
        );
    }

    /**
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->logo) {
            $attachments[] = Attachment::fromPath($this->logo)
                ->as('logoMarche.jpg')
                ->withMime('image/jpg');
        }

        return $attachments;
    }
}
