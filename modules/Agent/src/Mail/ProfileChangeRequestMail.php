<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Agent\Filament\Resources\Profiles\Pages\EditProfile;
use AcMarche\Hrm\Models\Employee;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ProfileChangeRequestMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Employee $employee,
        public readonly string $notes,
    ) {
        $this->subject = '[GRH] Changement de compte informatique - '.mb_trim(
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

        $fullName = mb_trim($this->employee->first_name.' '.$this->employee->last_name);

        $url = $this->employee->profile !== null
            ? EditProfile::getUrl(['record' => $this->employee->profile->getKey()], panel: 'agent-panel')
            : null;

        return new Content(
            view: 'agent::mail.profile_change_request',
            with: [
                'employee' => $this->employee,
                'employeeLabel' => $fullName,
                'notes' => $this->notes,
                'url' => $url,
                'logo' => $this->logo,
            ],
        );
    }
}
