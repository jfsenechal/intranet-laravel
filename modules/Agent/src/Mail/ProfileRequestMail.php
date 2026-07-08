<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Agent\Filament\Resources\Profiles\Pages\CreateProfile;
use AcMarche\Hrm\Models\Employee;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ProfileRequestMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Employee $employee,
    ) {
        $this->subject = '[GRH] Demande de compte informatique - '.mb_trim(
            $employee->first_name.' '.$employee->last_name
        );
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

        $employers = $this->employee->activeContracts
            ->map(fn ($contract) => $contract->employer?->name)
            ->filter()
            ->unique()
            ->implode(', ');

        $fullName = mb_trim($this->employee->first_name.' '.$this->employee->last_name);
        $label = $employers !== '' ? $fullName.' - '.$employers : $fullName;

        return new Content(
            view: 'agent::mail.profile_request',
            with: [
                'employee' => $this->employee,
                'employeeLabel' => $label,
                'url' => CreateProfile::getUrl(['employee_id' => $this->employee->id], panel: 'agent-panel'),
                'logo' => $this->logo,
            ],
        );
    }
}
