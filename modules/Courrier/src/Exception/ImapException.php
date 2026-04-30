<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Exception;

use Exception;

final class ImapException extends Exception
{
    public static function connectionFailed(string $message): self
    {
        return new self("IMAP connection failed: {$message}");
    }

    public static function messageNotFound(int $uid): self
    {
        return new self("Message with UID '{$uid}' not found");
    }

    public static function attachmentNotFound(int $uid, int $index): self
    {
        return new self("Attachment at index {$index} not found for message '{$uid}'");
    }

    public static function notConnected(): self
    {
        return new self('IMAP mailbox is not connected');
    }
}
