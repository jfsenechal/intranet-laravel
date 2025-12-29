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
}
