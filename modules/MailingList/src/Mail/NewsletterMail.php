<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Mail;

use AcMarche\MailingList\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Email $email,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->email->sender->email, $this->email->sender->name),
            subject: $this->email->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mailing-list-view::emails.newsletter',
            with: [
                'body' => $this->email->body,
                'recipientName' => $this->recipientName,
                'footer' => $this->email->sender->footer,
                'logoUrl' => $this->email->sender->logo
                    ? asset('storage/'.$this->email->sender->logo)
                    : null,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (empty($this->email->attachments)) {
            return [];
        }

        return collect($this->email->attachments)
            ->map(fn (string $path): Attachment => Attachment::fromStorageDisk('public', $path))
            ->all();
    }
}
