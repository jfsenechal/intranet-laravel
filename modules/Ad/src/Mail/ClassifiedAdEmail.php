<?php

declare(strict_types=1);

namespace AcMarche\Ad\Mail;

use AcMarche\Ad\Models\ClassifiedAd;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ClassifiedAdEmail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logo = null;

    public function __construct(public readonly ClassifiedAd $classifiedAd) {}

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
            html: 'ad::mail.ad-email',
            with: [
                'classifiedAd' => $this->classifiedAd,
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
                ->as('logoMarche.jpg')
                ->withMime('image/jpg');
        }
        foreach ($this->classifiedAd->medias as $path) {
            $attachments[] =
                Attachment::fromStorageDisk('public', $path);
            // ->as($media->name)
            //  ->withMime($media->mime);
        }

        return $attachments;
    }
}
