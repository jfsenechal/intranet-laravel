<?php

declare(strict_types=1);

namespace AcMarche\Agent\Mail;

use AcMarche\Agent\Filament\Resources\Profiles\Pages\EditProfile;
use AcMarche\Agent\Models\Profile;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a profile is delegated to somebody else so they complete it.
 */
final class ShareProfileMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Profile $profile,
        public readonly ?string $notes = null,
    ) {
        $this->subject = 'Profil pour '.$profile->fullName();
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
            view: 'agent::mail.share_profile',
            with: [
                'profile' => $this->profile,
                'profileLabel' => $this->profile->fullName(),
                'sender' => $this->senderAddress()->name ?? $this->senderAddress()->address,
                'notes' => $this->notes,
                'url' => EditProfile::getUrl(['record' => $this->profile->getKey()], panel: 'agent-panel'),
                'logo' => $this->logo,
            ],
        );
    }
}
