<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Mail;

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Mail\Concerns\ResolvesSenderAddress;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

final class IncomingMailNotification extends Mailable
{
    use Queueable, ResolvesSenderAddress, SerializesModels;

    /**
     * @param  Collection<int, IncomingMail>  $incomingMails
     */
    public function __construct(
        public readonly Recipient $recipient,
        public readonly Collection $incomingMails,
        public readonly bool $includeAttachments = false,
        public readonly ?CarbonInterface $mailDate = null,
    ) {}

    public function envelope(): Envelope
    {
        $subject = '[Indicateur] Notification de courriers entrants';

        if ($this->mailDate instanceof CarbonInterface) {
            $subject .= ' du '.$this->mailDate->format('d/m/Y');
        }

        return new Envelope(
            from: $this->senderAddress(),
            replyTo: config('mail.noreply_email'),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'courrier::mail.incoming-mail-notification',
            with: [
                'recipient' => $this->recipient,
                'incomingMails' => $this->incomingMails,
                'url' => url('/indicateur'),
            ],
        );
    }

    /**
     * Files attached to the notification.
     *
     * The listing itself may cover more mail than the recipient may download:
     * an index-role recipient is notified about every mail of the departments
     * they oversee. Each file is therefore checked against the same
     * AttachmentPolicy the download route enforces, so the mail never delivers
     * a document the recipient could not open in the application.
     *
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        if (! $this->includeAttachments) {
            return [];
        }

        $user = $this->recipientUser();

        if (! $user instanceof User) {
            return [];
        }

        $attachments = [];
        $disk = config('courrier.storage.disk', 'public');

        foreach ($this->incomingMails as $incomingMail) {
            foreach ($incomingMail->attachments as $attachment) {
                $path = $attachment->path;

                if ($path === null || ! Storage::disk($disk)->exists($path)) {
                    continue;
                }

                if (Gate::forUser($user)->denies('download', $attachment)) {
                    continue;
                }

                $attachments[] = Attachment::fromStorageDisk($disk, $path)
                    ->as($attachment->file_name)
                    ->withMime($attachment->mime);
            }
        }

        return $attachments;
    }

    /**
     * The intranet user behind the recipient, used to authorize the files.
     *
     * A recipient without a matching user cannot download anything in the
     * application, so they receive no attachment either.
     */
    private function recipientUser(): ?User
    {
        if (! $this->recipient->username) {
            return null;
        }

        return User::query()
            ->where('username', $this->recipient->username)
            ->first();
    }
}
