<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Mail;

use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\HrValidateTelework;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class TeleworkHrValidationMail extends Mailable
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Telework $telework,
        public readonly ?Employee $employee,
    ) {
        $this->subject = '[GRH] Télétravail validé par la direction - à traiter';
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
            view: 'hrm::mail.telework.hr_validation',
            with: [
                'telework' => $this->telework,
                'employee' => $this->employee,
                'url' => HrValidateTelework::getUrl(['record' => $this->telework]),
                'logo' => $this->logo,
            ],
        );
    }
}
