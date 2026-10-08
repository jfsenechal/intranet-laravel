<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Mail;

use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

final class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Email $email,
        public EmailRecipient $recipient,
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
            view: 'mailing-list::emails.newsletter',
            with: [
                'body' => $this->email->body,
                'recipientName' => $this->recipient->name ?? '',
                'footer' => $this->email->sender->footer,
                'logoUrl' => $this->email->sender->logo
                    ? asset('storage/'.$this->email->sender->logo)
                    : null,
                'unsubscribeUrl' => $this->email->unsubscribe_enabled && $this->recipient->exists
                    ? $this->recipient->unsubscribeUrl()
                    : null,
            ],
        );
    }

    public function headers(): Headers
    {
        if (! $this->email->unsubscribe_enabled || ! $this->recipient->exists) {
            return new Headers();
        }

        $oneClickUrl = URL::signedRoute('unsubscribe.store', ['recipient' => $this->recipient->getKey()]);

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$oneClickUrl.'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
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
