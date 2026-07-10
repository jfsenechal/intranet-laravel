<?php

declare(strict_types=1);

namespace AcMarche\News\Mail;

use AcMarche\News\Models\News;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewsEmail extends Mailable implements ShouldQueue
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    public ?string $logo = null;

    /**
     * @param  bool  $attachMedias  When false, the news medias are not attached to the
     *                              email and a notice with a link to the intranet is shown instead.
     */
    public function __construct(
        public readonly News $news,
        public readonly bool $attachMedias = true,
    ) {
        $this->captureSenderAddress();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
            replyTo: config('mail.noreply_email'),
            subject: '[Actu] '.$this->news->name,
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
            html: 'news::mail.news',
            with: [
                'news' => $this->news,
                'url' => url('/'),
                'logo' => $this->logo,
                'attachMedias' => $this->attachMedias,
                'mediasCount' => count($this->news->medias ?? []),
            ],
        );
    }

    /**
     * The logo is embedded inline in the body (see the Blade view), so it is not
     * attached here. Only the news medias are attached, and only when the recipient
     * has opted in to receiving attachments.
     *
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attachMedias) {
            return [];
        }

        return array_map(
            fn (string $path): Attachment => Attachment::fromStorageDisk('public', $path),
            $this->news->medias ?? [],
        );
    }
}
