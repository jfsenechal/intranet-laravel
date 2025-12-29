<?php

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Dto\EmailAttachment;
use AcMarche\Courrier\Dto\EmailMessage;
use AcMarche\Courrier\Dto\MailboxQuota;
use AcMarche\Courrier\Exception\ImapException;
use DirectoryTree\ImapEngine\Address;
use DirectoryTree\ImapEngine\Attachment;
use DirectoryTree\ImapEngine\Collections\FolderCollection;
use DirectoryTree\ImapEngine\Enums\ImapFetchIdentifier;
use DirectoryTree\ImapEngine\FolderInterface;
use DirectoryTree\ImapEngine\Laravel\Facades\Imap;
use DirectoryTree\ImapEngine\MailboxInterface;
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

    /**
     * @throws ImapException
     */
    public function connect(): void
    {
        if ($this->isConnected()) {
            return;
        }

        try {
            $this->mailbox = Imap::mailbox('imap_ville');
        } catch (Exception $e) {
            report($e);
            throw ImapException::connectionFailed($e->getMessage());
        }
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
     * @return array<int, EmailMessage>
     *
     * @throws ImapException
     */
    public function getMessages(int $daysBack = self::DEFAULT_DAYS_BACK): array
    {
        $this->ensureConnected();

        $messages = $this->mailbox
            ->inbox()
            ->messages()
            ->since(now()->subDays($daysBack))
            ->withHeaders()
            ->withBody()
            ->get();

        return collect($messages)
            ->map(fn (MessageInterface $message): EmailMessage => $this->mapToEmailMessage($message))
            ->toArray();
    }

    /**
     * @throws ImapException
     */
    public function findMessageByUid(string $uid): ?MessageInterface
    {
        $this->ensureConnected();

        return $this->mailbox
            ->inbox()
            ->messages()
            ->withBody()
            ->withHeaders()
            ->withFlags()
            ->find($uid, ImapFetchIdentifier::Uid);
    }

    /**
     * @throws ImapException
     */
    public function deleteMessage(string $uid): void
    {
        $message = $this->findMessageByUid($uid);

        if (! $message) {
            throw ImapException::messageNotFound($uid);
        }

        $message->markDeleted(true);
    }

    /**
     * @param  array<int, string>  $uids
     *
     * @throws ImapException
     */
    public function deleteMessages(array $uids): void
    {
        foreach ($uids as $uid) {
            $this->deleteMessage($uid);
        }
    }

    /**
     * @throws ImapException
     */
    public function getFolder(string $folderName): FolderInterface
    {
        $this->ensureConnected();

        return $this->mailbox->folders()->findOrFail($folderName);
    }

    /**
     * @throws ImapException
     */
    public function listFolders(): FolderCollection
    {
        $this->ensureConnected();

        return $this->mailbox->folders()->get();
    }

    /**
     * @throws ImapException
     */
    public function getAttachment(string $uid, int $attachmentIndex): Attachment
    {
        $this->ensureConnected();

        $message = $this->findMessageByUid($uid);

        if (! $message) {
            throw ImapException::messageNotFound($uid);
        }

        $attachments = $message->attachments();

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
        $this->ensureConnected();

        $data = $this->mailbox->inbox()->quota();
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

        if (! $this->mailbox) {
            throw ImapException::notConnected();
        }
    }

    private function mapToEmailMessage(MessageInterface $message): EmailMessage
    {
        return new EmailMessage(
            uid: $message->uid(),
            date: $message->date()?->format('d/m/Y H:i') ?? '',
            from: $this->formatAddress($message->from()),
            fromEmail: $message->from()?->email() ?? '',
            fromName: $message->from()?->name() ?? '',
            subject: $message->subject() ?? 'Sans objet',
            hasAttachments: $message->hasAttachments(),
            attachmentCount: $message->attachmentCount(),
            html: $message->html(),
            text: $message->text(),
            attachments: $this->mapAttachments($message->attachments()),
        );
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
            ->toArray();
    }

    private function formatAddress(?Address $address): string
    {
        if (! $address) {
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
