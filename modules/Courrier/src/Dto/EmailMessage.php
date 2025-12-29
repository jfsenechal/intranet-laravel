<?php

namespace AcMarche\Courrier\Dto;

final readonly class EmailMessage
{
    /**
     * @param  array<int, EmailAttachment>  $attachments
     */
    public function __construct(
        public string $uid,
        public string $date,
        public string $from,
        public string $fromEmail,
        public string $fromName,
        public string $subject,
        public bool $hasAttachments,
        public int $attachmentCount,
        public ?string $html,
        public ?string $text,
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
            'from' => $this->from,
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'subject' => $this->subject,
            'has_attachments' => $this->hasAttachments,
            'attachment_count' => $this->attachmentCount,
            'html' => $this->html,
            'text' => $this->text,
            'attachments' => array_map(
                fn (EmailAttachment $attachment): array => $attachment->toArray(),
                $this->attachments
            ),
        ];
    }
}
