<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

final readonly class MailboxQuota
{
    public function __construct(
        public int $usage,
        public int $limit,
        public float $percentage,
    ) {}
}
