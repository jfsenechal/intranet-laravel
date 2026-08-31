<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Jobs;

use AcMarche\Courrier\Ai\IncomingMailAnalyzer;
use AcMarche\Courrier\Dto\MailAnalysis;
use AcMarche\Courrier\Dto\MailSuggestion;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Handler\IncomingMailHandler;
use AcMarche\Courrier\Mail\IncomingMailDraftsReady;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\ImapRepository;
use AcMarche\Courrier\Repository\ServiceRepository;
use AcMarche\Courrier\Search\SuggestsMailRouting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

use function sprintf;

/**
 * Turn the Inbox messages an administrator ticked into draft incoming mails.
 *
 * Each attachment is read by the AI and saved as a draft: everything the model
 * proposed is stored, but the mail stays out of the day's listing, out of the
 * recipients' notifications and out of the search index until a human has read
 * it. When the whole batch is done the administrator is mailed a link to the
 * first draft, and validating one walks them to the next.
 *
 * A single job rather than one per message: the mail is only worth sending once
 * the batch is finished, and an analysis takes tens of seconds, so a batch of
 * twenty runs for several minutes — hence the generous timeout.
 */
final class AnalyzeInboxMessagesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 3600;

    /**
     * Analysing twice would create the drafts twice over, since nothing links a
     * draft back to the IMAP message it came from once that message is deleted.
     */
    public int $tries = 1;

    /**
     * @param  list<array{uid: int, index: int, filename: string, mime: string}>  $messages
     */
    public function __construct(
        public readonly string $mailbox,
        public readonly array $messages,
        public readonly int $userId,
        public readonly ?DepartmentCourrierEnum $department = null,
    ) {}

    public function handle(IncomingMailAnalyzer $analyzer, SuggestsMailRouting $router): void
    {
        $user = User::query()->find($this->userId);

        if (! $user instanceof User) {
            return;
        }

        $imapRepository = new ImapRepository($this->mailbox);

        /** @var Collection<int, IncomingMail> $drafts */
        $drafts = new Collection();

        /** @var list<string> $failures */
        $failures = [];

        foreach ($this->messages as $message) {
            try {
                $drafts->push($this->createDraft($analyzer, $router, $imapRepository, $message, $user));
            } catch (Throwable $throwable) {
                report($throwable);
                $failures[] = $message['filename'];
            }
        }

        $this->notifyAuthor($user, $drafts, $failures);
    }

    /**
     * Shown on the failed-jobs listing, where the mailbox alone says little.
     */
    public function displayName(): string
    {
        return sprintf('%s (%d message(s), %s)', self::class, count($this->messages), $this->mailbox);
    }

    /**
     * @param  array{uid: int, index: int, filename: string, mime: string}  $message
     */
    private function createDraft(
        IncomingMailAnalyzer $analyzer,
        SuggestsMailRouting $router,
        ImapRepository $imapRepository,
        array $message,
        User $user,
    ): IncomingMail {
        $attachment = $imapRepository->getAttachment($message['uid'], $message['index']);
        $contents = $attachment->contents();

        $analysis = $this->analyze($analyzer, $contents, $message);
        $suggestion = $analysis->suggestion;

        $incomingMail = IncomingMail::create([
            // The reference number is read off the reception stamp; when the
            // model cannot find it the field is left empty and the form, which
            // requires it, stops the user from validating without one. This
            // holds for every department, CPAS included.
            'reference_number' => $suggestion->referenceNumber,
            'sender' => $suggestion->sender,
            // The analysis does not date the letter, and the mail room encodes
            // what it receives today; the user corrects it while verifying.
            'mail_date' => today(),
            'description' => $suggestion->description,
            // Stored now rather than left to the indexing job: the draft's
            // routing suggestions are looked up from this text, and the user
            // may well open the draft before that job has run.
            'content' => $analysis->documentText,
            'is_registered' => $suggestion->isRegistered,
            'has_acknowledgment' => $suggestion->hasAcknowledgment,
            'is_notified' => false,
            'is_draft' => true,
            // Set explicitly: the job runs with no authenticated user, so
            // neither HasUserAdd nor the department default can fill them.
            'department' => $this->department?->value,
            'user_add' => $user->username,
        ]);

        $this->attachServices($incomingMail, $suggestion);
        $this->attachRouting($incomingMail, $router, $analysis);

        IncomingMailHandler::storeAttachmentContents(
            $incomingMail,
            $contents,
            $message['filename'],
            $message['mime'],
        );

        $this->deleteMessage($imapRepository, $message['uid']);

        // The job dispatched by the `created` event ran before the services and
        // the attachment existed. It skips drafts, but the record still has to
        // be complete before the user validates it.
        IndexIncomingMailJob::dispatch($incomingMail->id)->afterCommit();

        return $incomingMail;
    }

    /**
     * The analyser reads a file, and the document only exists on the IMAP
     * server, so it is written out first. The extension is kept: pdftotext and
     * Tesseract both branch on it.
     *
     * @param  array{uid: int, index: int, filename: string, mime: string}  $message
     */
    private function analyze(IncomingMailAnalyzer $analyzer, string $contents, array $message): MailAnalysis
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'courrier_ai_');

        if ($temporaryPath === false) {
            throw new RuntimeException('Impossible de créer un fichier temporaire pour l\'analyse.');
        }

        $extension = pathinfo($message['filename'], PATHINFO_EXTENSION);
        if ($extension !== '') {
            $renamed = $temporaryPath.'.'.$extension;
            rename($temporaryPath, $renamed);
            $temporaryPath = $renamed;
        }

        file_put_contents($temporaryPath, $contents);

        try {
            return $analyzer->analyze($temporaryPath, $message['mime']);
        } finally {
            @unlink($temporaryPath);
        }
    }

    /**
     * The stamp names the destination services by their initials. Codes that
     * match no service of the department, or more than one, are dropped rather
     * than guessed — the user picks those while verifying the draft.
     */
    private function attachServices(IncomingMail $incomingMail, MailSuggestion $suggestion): void
    {
        foreach (ServiceRepository::findIdsByCodes($suggestion->services, $this->department) as $serviceId) {
            $incomingMail->services()->attach($serviceId, ['is_primary' => true]);
        }
    }

    /**
     * Route the draft the way comparable mail was routed.
     *
     * Where a courrier goes is not written on the paper, so it is retrieved
     * from the 166.000 already encoded rather than read by the model. The two
     * best candidates per field are written straight into the draft, which
     * nobody sees until a human has verified it — the services only when the
     * reception stamp named none, since the stamp is the mail room's own word
     * and outranks a retrieval.
     */
    private function attachRouting(
        IncomingMail $incomingMail,
        SuggestsMailRouting $router,
        MailAnalysis $analysis,
    ): void {
        if ($analysis->documentText === '') {
            return;
        }

        $routing = $router->suggest(
            $analysis->documentText,
            $analysis->suggestion->sender,
            $this->department?->value,
            $incomingMail->id,
        );

        foreach ($routing->topRecipientIds() as $recipientId) {
            $incomingMail->recipients()->attach($recipientId, ['is_primary' => true]);
        }

        if ($incomingMail->services()->exists()) {
            return;
        }

        foreach ($routing->topServiceIds() as $serviceId) {
            $incomingMail->services()->attach($serviceId, ['is_primary' => true]);
        }
    }

    /**
     * The message is removed like the single-attachment "Traiter" action does:
     * the document now lives on the storage disk. A failure here is logged and
     * does not fail the draft, which exists and is the point of the batch.
     */
    private function deleteMessage(ImapRepository $imapRepository, int $uid): void
    {
        try {
            $imapRepository->deleteMessage($uid);
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }

    /**
     * @param  Collection<int, IncomingMail>  $drafts
     * @param  list<string>  $failures
     */
    private function notifyAuthor(User $user, Collection $drafts, array $failures): void
    {
        if (blank($user->email)) {
            return;
        }

        try {
            Mail::to(new Address($user->email))->send(new IncomingMailDraftsReady($drafts, $failures));
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
