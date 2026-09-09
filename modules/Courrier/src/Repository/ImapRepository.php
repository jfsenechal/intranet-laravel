<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Dto\EmailAttachment;
use AcMarche\Courrier\Dto\EmailMessage;
use AcMarche\Courrier\Dto\MailboxQuota;
use AcMarche\Courrier\Exception\ImapException;
use DirectoryTree\ImapEngine\Address;
use DirectoryTree\ImapEngine\Attachment;
use DirectoryTree\ImapEngine\Collections\FolderCollection;
use DirectoryTree\ImapEngine\Enums\ImapFetchIdentifier;
use DirectoryTree\ImapEngine\Exceptions\Exception as ImapEngineException;
use DirectoryTree\ImapEngine\FolderInterface;
use DirectoryTree\ImapEngine\Laravel\Facades\Imap;
use DirectoryTree\ImapEngine\MailboxInterface;
use DirectoryTree\ImapEngine\Message;
use DirectoryTree\ImapEngine\MessageInterface;
use Exception;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ImapRepository
{
    public const string FOLDER_INBOX = 'INBOX';

    public const string FOLDER_TRASH = 'INBOX/Trash';

    private const int DEFAULT_DAYS_BACK = 10;

    private ?MailboxInterface $mailbox = null;

    public function __construct(private readonly string $mailboxName = 'imap_ville') {}

    /**
     * The name the mailbox is registered under (e.g. `imap_cpas`).
     */
    public function mailboxName(): string
    {
        return $this->mailboxName;
    }

    /**
     * Open the session now, rather than letting the library open it on the
     * first command.
     *
     * `Imap::mailbox()` only builds the object; ImapEngine connects and logs in
     * lazily, so without the explicit `connect()` a bad host or a refused login
     * surfaced from `inbox()` deep inside a table render instead of from here.
     *
     * @throws ImapException
     */
    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        try {
            $mailbox = Imap::mailbox($this->mailboxName);
            $mailbox->connect();
        } catch (Exception $e) {
            $this->mailbox = null;
            report($e);
            throw ImapException::connectionFailed($e->getMessage());
        }

        $this->mailbox = $mailbox;
    }

    public function disconnect(): void
    {
        if ($this->isConnected()) {
            $this->mailbox->disconnect();
            $this->mailbox = null;
        }
    }

    public function isConnected(): bool
    {
        return $this->mailbox?->connected() ?? false;
    }

    /**
     * List the recent messages of the inbox, headers and attachment metadata only.
     *
     * `BODYSTRUCTURE` describes the MIME parts without transferring them, so a
     * mailbox of scanned PDFs costs a few kilobytes here instead of the tens of
     * megabytes `withBody()` would download to render a table of subjects. The
     * bodies of the listed messages are fetched one at a time, on demand, by
     * `getMessageBody()` and `getAttachment()`.
     *
     * @return array<int, EmailMessage>
     *
     * @throws ImapException
     */
    public function getMessages(int $daysBack = self::DEFAULT_DAYS_BACK): array
    {
        return $this->onMailbox(function (MailboxInterface $mailbox) use ($daysBack): array {
            $messages = $mailbox
                ->inbox()
                ->messages()
                ->since(now()->subDays($daysBack))
                ->withHeaders()
                ->withBodyStructure()
                ->get();

            return collect($messages)
                ->map(fn (MessageInterface $message): EmailMessage => $this->mapToEmailMessage($message))
                ->all();
        });
    }

    /**
     * Read the rendered body of a single message.
     *
     * Kept out of `getMessages()`: only the message the user opens needs it.
     *
     * @return array{html: ?string, text: ?string}
     *
     * @throws ImapException
     */
    public function getMessageBody(int $uid): array
    {
        return $this->onMailbox(function (MailboxInterface $mailbox) use ($uid): array {
            $message = $mailbox
                ->inbox()
                ->messages()
                ->withHeaders()
                ->withBody()
                ->find($uid, ImapFetchIdentifier::Uid);

            if (! $message instanceof MessageInterface) {
                throw ImapException::messageNotFound($uid);
            }

            return [
                'html' => $message->html(),
                'text' => $message->text(),
            ];
        });
    }

    /**
     * Locate a message by UID without downloading its body.
     *
     * Callers that need content read it through the body structure, which lets
     * them pull the one MIME part they want rather than the whole message.
     *
     * @throws ImapException
     */
    public function findMessageByUid(int $uid): ?MessageInterface
    {
        return $this->onMailbox(fn (MailboxInterface $mailbox): ?MessageInterface => $mailbox
            ->inbox()
            ->messages()
            ->withHeaders()
            ->withBodyStructure()
            ->withFlags()
            ->find($uid, ImapFetchIdentifier::Uid));
    }

    /**
     * @throws ImapException
     */
    public function deleteMessage(int $uid): void
    {
        $message = $this->findMessageByUid($uid);

        if (! $message instanceof MessageInterface) {
            throw ImapException::messageNotFound($uid);
        }

        $this->onMailbox(function () use ($message): void {
            $message->markDeleted(true);
        });
    }

    /**
     * @param  array<int, int|string>  $uids
     *
     * @throws ImapException
     */
    public function deleteMessages(array $uids): void
    {
        if ($uids === []) {
            return;
        }

        $this->onMailbox(function (MailboxInterface $mailbox) use ($uids): void {
            $mailbox
                ->inbox()
                ->messages()
                ->destroy(array_map('intval', $uids), expunge: true);
        });
    }

    /**
     * @throws ImapException
     */
    public function getFolder(string $folderName): FolderInterface
    {
        return $this->onMailbox(fn (MailboxInterface $mailbox): FolderInterface => $mailbox->folders()->findOrFail($folderName));
    }

    /**
     * @throws ImapException
     */
    public function listFolders(): FolderCollection
    {
        return $this->onMailbox(fn (MailboxInterface $mailbox): FolderCollection => $mailbox->folders()->get());
    }

    /**
     * @throws ImapException
     */
    public function getAttachment(int $uid, int $attachmentIndex): Attachment
    {
        $message = $this->findMessageByUid($uid);

        if (! $message instanceof MessageInterface) {
            throw ImapException::messageNotFound($uid);
        }

        $attachments = $this->onMailbox(fn (): array => $this->attachmentsOf($message));

        if (! isset($attachments[$attachmentIndex])) {
            throw ImapException::attachmentNotFound($uid, $attachmentIndex);
        }

        return $attachments[$attachmentIndex];
    }

    /**
     * @throws ImapException
     */
    public function getQuota(): MailboxQuota
    {
        $data = $this->onMailbox(fn (MailboxInterface $mailbox): array => $mailbox->inbox()->quota());
        $usage = $data['INBOX']['STORAGE']['usage'];
        $limit = $data['INBOX']['STORAGE']['limit'];

        return new MailboxQuota(
            usage: $usage,
            limit: $limit,
            percentage: $limit > 0 ? ($usage * 100) / $limit : 0,
        );
    }

    public function createAttachmentDownloadResponse(Attachment $attachment): StreamedResponse
    {
        $stream = $attachment->contentStream();
        $filename = $attachment->filename() ?? 'attachment';
        $mimeType = $attachment->contentType() ?? 'application/octet-stream';
        $size = $stream->getSize();

        $response = new StreamedResponse(function () use ($stream): void {
            $outputStream = fopen('php://output', 'wb');

            if ($outputStream === false) {
                return;
            }

            while (! $stream->eof()) {
                fwrite($outputStream, $stream->read(8192));
                flush();
            }

            fclose($outputStream);

            if (method_exists($stream, 'close')) {
                $stream->close();
            }
        });

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $filename,
                $this->sanitizeFilename($filename)
            )
        );

        if ($size !== null) {
            $response->headers->set('Content-Length', (string) $size);
        }

        return $response;
    }

    /**
     * @throws ImapException
     */
    private function ensureConnected(): void
    {
        if (! $this->isConnected()) {
            $this->connect();
        }

        if (! $this->mailbox instanceof MailboxInterface) {
            throw ImapException::notConnected();
        }
    }

    /**
     * Run an operation against the connected mailbox, as the single place where
     * ImapEngine failures become an `ImapException`.
     *
     * ImapEngine throws its own exception hierarchy, which no caller catches:
     * a server closing the connection mid-command escaped every
     * `catch (ImapException)` and reached the user as a Livewire 500 while the
     * Inbox table was rendering. Every command goes through here so a broken
     * session always leaves as an `ImapException`.
     *
     * @template TReturn
     *
     * @param  callable(MailboxInterface): TReturn  $operation
     * @return TReturn
     *
     * @throws ImapException
     */
    private function onMailbox(callable $operation): mixed
    {
        $this->ensureConnected();

        try {
            return $operation($this->mailbox);
        } catch (ImapEngineException $e) {
            report($e);
            throw ImapException::operationFailed($e->getMessage());
        }
    }

    private function mapToEmailMessage(MessageInterface $message): EmailMessage
    {
        $attachments = $this->attachmentsOf($message);

        return new EmailMessage(
            uid: $message->uid(),
            date: display_datetime($message->date()) ?? '',
            timestamp: $message->date()?->getTimestamp() ?? 0,
            from: $this->formatAddress($message->from()),
            fromEmail: $message->from()?->email() ?? '',
            fromName: $message->from()?->name() ?? '',
            subject: $message->subject() ?? 'Sans objet',
            hasAttachments: $attachments !== [],
            attachmentCount: count($attachments),
            attachments: $this->mapAttachments($attachments),
        );
    }

    /**
     * The attachments of a message, read from its body structure when one was
     * fetched.
     *
     * `Message::attachments(fetch: true)` builds them from the body structure
     * and defers each part's download until it is read, which is what makes
     * listing a mailbox cheap. Only the concrete `Message` accepts the flag;
     * `FakeMessage` (tests) parses the raw source it was handed instead.
     *
     * @return array<int, Attachment>
     */
    private function attachmentsOf(MessageInterface $message): array
    {
        return $message instanceof Message
            ? $message->attachments(fetch: true)
            : $message->attachments();
    }

    /**
     * @param  array<int, Attachment>  $attachments
     * @return array<int, EmailAttachment>
     */
    private function mapAttachments(array $attachments): array
    {
        return collect($attachments)
            ->map(fn (Attachment $attachment): EmailAttachment => new EmailAttachment(
                filename: $attachment->filename() ?? 'Sans nom',
                contentType: $attachment->contentType(),
                extension: $attachment->extension(),
            ))
            ->all();
    }

    private function formatAddress(?Address $address): string
    {
        if (! $address instanceof Address) {
            return '';
        }

        $name = $address->name();
        $email = $address->email();

        if ($name && $name !== $email) {
            return sprintf('%s <%s>', $name, $email);
        }

        return $email;
    }

    private function sanitizeFilename(string $filename): string
    {
        $sanitized = preg_replace('/[^\x20-\x7E]/', '', $filename);

        return $sanitized ?: 'downloaded_file';
    }
}
