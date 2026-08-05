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
     * Files the recipient is allowed to receive, resolved once and shared by
     * content() and attachments(): the view has to know whether the files made
     * it into the message, and Laravel may call either method first.
     *
     * @var array<Attachment>|null
     */
    private ?array $resolvedAttachments = null;

    private int $attachmentsCount = 0;

    private int $attachmentsSize = 0;

    private bool $attachmentsOmitted = false;

    /**
     * @param  Collection<int, IncomingMail>  $incomingMails
     */
    public function __construct(
        public readonly Recipient $recipient,
        public readonly Collection $incomingMails,
        public readonly bool $includeAttachments = false,
        public readonly ?CarbonInterface $mailDate = null,
        public readonly bool $attachmentsUnavailable = false,
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
        $this->resolveAttachments();

        return new Content(
            html: 'courrier::mail.incoming-mail-notification',
            with: [
                'recipient' => $this->recipient,
                'incomingMails' => $this->incomingMails,
                'url' => url('/indicateur'),
                'attachmentsCount' => $this->attachmentsCount,
                'attachmentsSize' => $this->attachmentsSize,
                'attachmentsOmitted' => $this->attachmentsOmitted,
                'attachmentsUnavailable' => $this->attachmentsUnavailable,
            ],
        );
    }

    /**
     * @return array<Attachment>
     */
    public function attachments(): array
    {
        return $this->resolveAttachments();
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
     * A day's worth of scanned mail can outgrow the `message_size_limit` of the
     * SMTP server, which then rejects the whole message (552 5.3.4) and leaves
     * the recipient with no notification at all. The total is therefore weighed
     * up front: past the limit nothing is attached and the view explains why,
     * so the listing always reaches its recipient.
     *
     * @return array<Attachment>
     */
    private function resolveAttachments(): array
    {
        if ($this->resolvedAttachments !== null) {
            return $this->resolvedAttachments;
        }

        $this->resolvedAttachments = [];

        if (! $this->includeAttachments) {
            return [];
        }

        $user = $this->recipientUser();

        if (! $user instanceof User) {
            return [];
        }

        $diskName = config('courrier.storage.disk', 'public');
        $disk = Storage::disk($diskName);

        $attachments = [];

        foreach ($this->incomingMails as $incomingMail) {
            foreach ($incomingMail->attachments as $attachment) {
                $path = $attachment->path;

                if ($path === null || ! $disk->exists($path)) {
                    continue;
                }

                if (Gate::forUser($user)->denies('download', $attachment)) {
                    continue;
                }

                $this->attachmentsSize += $disk->size($path);

                $attachments[] = Attachment::fromStorageDisk($diskName, $path)
                    ->as($attachment->file_name)
                    ->withMime($attachment->mime);
            }
        }

        $this->attachmentsCount = count($attachments);

        if ($this->exceedsMessageSizeLimit()) {
            $this->attachmentsOmitted = true;

            return [];
        }

        return $this->resolvedAttachments = $attachments;
    }

    /**
     * Whether the encoded message would be refused by the SMTP server.
     */
    private function exceedsMessageSizeLimit(): bool
    {
        if ($this->attachmentsSize === 0) {
            return false;
        }

        $limit = (int) config('courrier.mail.message_size_limit');
        $overhead = (float) config('courrier.mail.encoding_overhead', 1.37);

        return $this->attachmentsSize * $overhead > $limit;
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
