<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Mail;

use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ManagerValidateTelework;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TeleworkManagerValidationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Telework $telework,
        public readonly Employee $employee,
        public readonly Employee $director,
    ) {
        $this->subject = '[GRH] Nouvelle demande de télétravail à valider';
        $this->captureSenderAddress();
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
            view: 'hrm::mail.telework.manager_validation',
            with: [
                'telework' => $this->telework,
                'employee' => $this->employee,
                'director' => $this->director,
                'url' => ManagerValidateTelework::getUrl(['record' => $this->telework]),
                'logo' => $this->logo,
            ],
        );
    }
}
