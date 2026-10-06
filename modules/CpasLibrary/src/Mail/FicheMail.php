<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Mail;

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ViewFiche;
use AcMarche\CpasLibrary\Models\Fiche;
use App\Mail\Concerns\ResolvesSenderAddress;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class FicheMail extends Mailable
{
    use Queueable;
    use ResolvesSenderAddress;
    use SerializesModels;

    public ?string $logo = null;

    public function __construct(
        public readonly Fiche $fiche,
        public readonly string $url,
    ) {
        $this->subject = '[Bibliothèque CPAS] '.$fiche->name;
        $this->captureSenderAddress();
    }

    /**
     * Mail the fiche to every user holding a library role, one message per
     * recipient so addresses are not disclosed to each other.
     *
     * @return int The number of queued mails.
     */
    public static function sendToLibraryUsers(Fiche $fiche): int
    {
        $recipients = User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', [
                RolesEnum::ROLE_LIBRARY->value,
                RolesEnum::ROLE_LIBRARY_ADMIN->value,
            ]))
            ->whereNotNull('email')
            ->get();

        $url = ViewFiche::getUrl(['record' => $fiche]);

        foreach ($recipients as $recipient) {
            Mail::to(new Address($recipient->email, $recipient->fullNameAsString()))
                ->queue(new self($fiche, $url));
        }

        return $recipients->count();
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
            view: 'cpas-library::mail.fiche',
            with: [
                'fiche' => $this->fiche,
                'url' => $this->url,
                'logo' => $this->logo,
            ],
        );
    }
}
