<?php

namespace AcMarche\Courrier\Repository;

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
    final public const INBOX = 'INBOX';

    final public const TRASH = 'INBOX/Trash';

    private ?MailboxInterface $mailbox = null;

    public function connectImap(): void
    {
        Imap::register('imap_ville', [
            'host' => config('courrier.imap.ville.host'),
            'port' => config('courrier.imap.ville.port', 993),
            'username' => config('courrier.imap.ville.username', ''),
            'password' => config('courrier.imap.ville.password', ''),
            'encryption' => config('courrier.imap.ville.encryption', 'ssl'),
        ]);

        try {
            $this->mailbox = Imap::mailbox('imap_ville');

        } catch (Exception $e) {
            report($e);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMessages(): array
    {
        $inbox = $this->mailbox->inbox();
        $messages = $inbox->messages()
            ->since(now()->subDays(10))
            ->withHeaders()
            ->withBody()
            ->get();

        return collect($messages)
            ->map(fn(MessageInterface $message): array => [
                'uid' => $message->uid(),
                'date' => $message->date()?->format('d/m/Y H:i') ?? '',
                'from' => self::formatAddress($message->from()),
                'from_email' => $message->from()?->email() ?? '',
                'from_name' => $message->from()?->name() ?? '',
                'subject' => $message->subject() ?? 'Sans objet',
                'has_attachments' => $message->hasAttachments(),
                'attachment_count' => $message->attachmentCount(),
                'html' => $message->html(),
                'text' => $message->text(),
                'attachments' => collect($message->attachments())
                    ->map(fn($attachment): array => [
                        'filename' => $attachment->filename() ?? 'Sans nom',
                        'content_type' => $attachment->contentType(),
                        'extension' => $attachment->extension(),
                    ])
                    ->toArray(),
            ])
            ->toArray();

    }

    public function message(
        string $uid
    ): ?MessageInterface {
        $inbox = $this->mailbox->inbox();

        return $inbox->messages()
            ->withHeaders()
            ->withBody()
            ->find($uid);
    }

    public function getMessageByUid(
        string $uid
    ): ?MessageInterface {
        return $this->mailbox
            ->inbox()
            ->messages()
            ->withBody()
            ->withHeaders()
            ->withFlags()
            ->find($uid, ImapFetchIdentifier::Uid);
    }

    /**
     * @throws Exception
     */
    public function deleteMessage(
        string $uid
    ): void {
        $message = $this->getMessageByUid($uid);
        if (!$message) {
            throw new Exception('Message not found');
        }
        $message?->markDeleted(true);
    }

    public function getFolder(
        string $folderName
    ): ?FolderInterface {
        $this->connectImap();

        return $this->mailbox->folders()->findOrFail($folderName);
    }

    public function listFolders(): FolderCollection
    {
        $this->connectImap();

        return $this->mailbox->folders()->get();
    }

    /**
     * @throws Exception
     */
    public function getAttachment(
        string $uid,
        int $attachmentIndex
    ): ?Attachment {
        $this->connectImap();
        $message = $this->getMessageByUid($uid);
        if (!$message) {
            throw new Exception('Message not found');
        }

        $attachment = $message->attachments()[$attachmentIndex];

        if (!$attachment) {
            throw new Exception('Attachment not found');
        }

        return $attachment;
    }

    /**
     * @return array{usage: int, limit: int, pourcentage: float}
     */
    public function getQuota(): array
    {
        $this->connectImap();

        $data = $this->mailbox->inbox()->quota();

        return [
            'usage' => $data['INBOX']['STORAGE']['usage'],
            'limit' => $data['INBOX']['STORAGE']['limit'],
            'pourcentage' => ($data['INBOX']['STORAGE']['usage'] * 100) / $data['INBOX']['STORAGE']['limit'],
        ];
    }

    public function close(): void
    {
        if ($this->mailbox?->connected()) {
            $this->mailbox->disconnect();
        }
    }

    public function downloadStreamAttachment(
        Attachment $attachment
    ): StreamedResponse {
        $stream = $attachment->contentStream();
        $filename = $attachment->filename();
        $mimeType = $attachment->contentType();
        $size = $stream->getSize();

        $response = new StreamedResponse(function () use ($stream) {
            $outputStream = fopen('php://output', 'wb');
            if ($outputStream === false) {
                error_log('Failed to open php://output');

                return;
            }

            while (!$stream->eof()) {
                fwrite($outputStream, $stream->read(8192));
                flush();
            }

            if (method_exists($stream, 'close')) {
                $stream->close();
            }
        });

        $response->headers->set('Content-Type', $mimeType);

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename,
            preg_replace('/[^\x20-\x7E]/', '', $filename) ?: 'downloaded_file'
        );
        $response->headers->set('Content-Disposition', $disposition);

        if ($size !== null) {
            $response->headers->set('Content-Length', $size);
        }

        return $response;
    }

    private static function formatAddress(
        ?Address $address
    ): string {
        if (!$address) {
            return '';
        }

        $name = $address->name();
        $email = $address->email();

        if ($name && $name !== $email) {
            return "{$name} <{$email}>";
        }

        return $email;
    }

    public function deleteMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $this->deleteMessage($message);
        }
    }
}
