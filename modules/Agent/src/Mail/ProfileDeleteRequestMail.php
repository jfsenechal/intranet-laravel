<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Hrm\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ProfileDeleteRequestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Employee $employee,
    ) {
        $this->subject = '[GRH] Suppression de compte informatique - '.mb_trim(
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
        $username = $this->employee->profile?->username;

        return new Content(
            view: 'agent::mail.profile_delete_request',
            with: [
                'employee' => $this->employee,
                'employeeLabel' => $fullName,
                'username' => $username,
                'logo' => $this->logo,
            ],
        );
    }
}
