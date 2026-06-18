<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Mail;

use AcMarche\Hrm\Models\Employee;
use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

final class PurgedApplicationsMail extends Mailable
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    /**
     * @param  Collection<int, Employee>  $candidates
     */
    public function __construct(public readonly Collection $candidates)
    {
        $this->subject = 'Les candidats suivants ont été supprimés';
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
            view: 'hrm::mail.purged-applications',
            with: [
                'candidates' => $this->candidates,
                'logo' => $this->logo,
            ],
        );
    }
}
