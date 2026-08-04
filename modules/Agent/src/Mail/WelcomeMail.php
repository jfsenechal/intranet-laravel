<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Agent\Filament\Exports\ProfilePdfExport;
use AcMarche\Agent\Filament\Resources\Profiles\Pages\ViewProfile;
use AcMarche\Agent\Models\Profile;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Welcome letter sent when a new account has been created, with the
 * printable welcome PDF attached.
 */
final class WelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Profile $profile,
        public readonly string $password,
        public readonly ?string $notes = null,
    ) {
        $this->subject = 'Nouveau compte AC/CPAS: '.$profile->fullName();
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
            view: 'agent::mail.welcome',
            with: [
                'profile' => $this->profile,
                'profileLabel' => $this->profile->fullName(),
                'notes' => $this->notes,
                'url' => ViewProfile::getUrl(['record' => $this->profile->getKey()], panel: 'agent-panel'),
                'logo' => $this->logo,
            ],
        );
    }

    /**
     * @return list<\Illuminate\Mail\Attachment>
     */
    public function attachments(): array
    {
        return [
            ProfilePdfExport::welcomeAttachment($this->profile, $this->password, $this->notes),
        ];
    }
}
