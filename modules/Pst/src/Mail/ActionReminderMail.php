<?php

declare(strict_types=1);

namespace AcMarche\Pst\Mail;

use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Models\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * https://maizzle.com/docs/components // todo
 */
final class ActionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public ?string $logo = null;

    public string $content;

    public function __construct(public readonly Action $action, array $data)
    {
        $this->subject = '[PST] '.$data['subject'];
        $this->content = $data['content'];
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
        if (! file_exists($this->logo)) {
            $this->logo = null;
        }

        return new Content(
            markdown: 'mail.action.reminder',
            with: [
                'action' => $this->action,
                'url' => ActionPstResource::getUrl('view', ['record' => $this->action]),
                'logo' => $this->logo,
                'content' => $this->content,
            ],
        );
    }
}
