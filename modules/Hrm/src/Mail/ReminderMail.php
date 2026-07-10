<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Mail;

use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ReminderMail extends Mailable
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly string $reminderType,
        public readonly Model $record,
        public readonly string $url,
        public readonly ?string $employeeName = null,
    ) {
        $this->subject = $employeeName !== null && $employeeName !== ''
            ? "[GRH] Rappel - {$reminderType} - {$employeeName}"
            : "[GRH] Rappel - {$reminderType}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->senderAddress(),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        $this->logo = public_path('images/Marche_logo.png');
        if (! file_exists($this->logo)) {
            $this->logo = null;
        }

        return new Content(
            view: 'hrm::mail.reminders.reminder',
            with: [
                'reminderType' => $this->reminderType,
                'record' => $this->record,
                'url' => $this->url,
                'employeeName' => $this->employeeName,
                'logo' => $this->logo,
            ],
        );
    }
}
