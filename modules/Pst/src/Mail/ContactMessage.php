<?php

declare(strict_types=1);

namespace AcMarche\Pst\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * https://maizzle.com/docs/components // todo
 */
final class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logo = null;

    public function __construct(public readonly string $content) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('APP_NAME')),
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
            markdown: 'mail.contact',
            with: [
                'content' => $this->content,
                'url' => url('/'),
                'logo' => $this->logo,
            ],
        );
    }
}
