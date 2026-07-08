<?php

declare(strict_types=1);

namespace AcMarche\Pst\Mail;

use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Models\Action;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * https://maizzle.com/docs/components // todo
 */
final class ActionReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    public ?string $logo = null;

    public string $content;

    public function __construct(public readonly Action $action, array $data)
    {
        $this->subject = '[PST] '.$data['subject'];
        $this->content = $data['content'];
        $this->captureSenderAddress();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
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
