<?php

declare(strict_types=1);

namespace AcMarche\Pst\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $htmlString) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A critical error occurred'
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlString
        );
    }
}
