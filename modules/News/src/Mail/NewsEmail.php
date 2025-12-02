<?php

namespace AcMarche\News\Mail;

use AcMarche\News\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logo = null;

    public function __construct(public readonly News $news)
    {
    }

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
        if (!file_exists($this->logo)) {
            $this->logo = null;
        }

        return new Content(
            html: 'news::mail.news',
            with: [
                'news' => $this->news,
                'url' => url('/'),
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
                ->as('logoMarcheur.jpg')
                ->withMime('image/jpg');
        }
        foreach ($this->news->medias as $path) {
            $attachments[] =
                Attachment::fromStorageDisk('public', $path);
            // ->as($media->name)
            //  ->withMime($media->mime);
        }

        return $attachments;
    }
}
