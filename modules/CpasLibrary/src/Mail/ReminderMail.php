<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Mail;

use App\Mail\Concerns\ResolvesSenderAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ReminderMail extends Mailable
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    /**
     * @param  Collection<int, \AcMarche\CpasLibrary\Models\Fiche>  $fiches
     * @param  array<int, string>  $urls  Fiche view URL indexed by fiche id.
     */
    public function __construct(
        public readonly Collection $fiches,
        public readonly array $urls = [],
    ) {
        $this->subject = '[Bibliothèque CPAS] Rappels du jour';
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
            view: 'cpas-library::mail.reminder',
            with: [
                'fiches' => $this->fiches,
                'urls' => $this->urls,
                'logo' => $this->logo,
            ],
        );
    }
}
