<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Mail;

use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ViewTelework;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TeleworkEmployeeManagerResultMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Telework $telework,
        public readonly Employee $employee,
    ) {
        $this->subject = $telework->manager_validated
            ? '[GRH] Votre télétravail a été validé par votre direction'
            : '[GRH] Votre télétravail a été refusé par votre direction';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), (string) config('app.name')),
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
            view: 'hrm::mail.telework.employee_manager_result',
            with: [
                'telework' => $this->telework,
                'employee' => $this->employee,
                'url' => ViewTelework::getUrl(['record' => $this->telework]),
                'logo' => $this->logo,
            ],
        );
    }
}
