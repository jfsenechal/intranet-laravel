<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

/**
 * A message as the Inbox listing knows it: headers plus attachment metadata.
 *
 * The body is deliberately absent — see `ImapRepository::getMessages()`. Read it
 * with `ImapRepository::getMessageBody()` for the one message being opened.
 */
final readonly class EmailMessage
{
    /**
     * @param  string  $date  Formatted for display; sort on `$timestamp` instead.
     * @param  array<int, EmailAttachment>  $attachments
     */
    public function __construct(
        public int $uid,
        public string $date,
        public int $timestamp,
        public string $from,
        public string $fromEmail,
        public string $fromName,
        public string $subject,
        public bool $hasAttachments,
        public int $attachmentCount,
        public array $attachments,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'uid' => $this->uid,
            'date' => $this->date,
            'timestamp' => $this->timestamp,
            'from' => $this->from,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'subject' => $this->subject,
            'has_attachments' => $this->hasAttachments,
            'attachment_count' => $this->attachmentCount,
            'attachments' => array_map(
                fn (EmailAttachment $attachment): array => $attachment->toArray(),
                $this->attachments
            ),
        ];
    }
}
