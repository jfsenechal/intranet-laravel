<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Agent\Filament\Resources\Profiles\Pages\EditProfile;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ProfileChangeRequestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Employee $employee,
        public readonly string $notes,
    ) {
        $this->subject = '[GRH] Changement de compte informatique - '.mb_trim(
            $employee->first_name.' '.$employee->last_name
        );
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
