<?php

declare(strict_types=1);

namespace AcMarche\App\Sms\Dto;

final class HistoricLine
{
    public function __construct(
        public ?string $date = null,
        public ?string $recipient = null,
        public ?string $user = null,
        public ?string $type = null,
        public ?string $ackDate = null,
        public ?string $statusText = null,
        public float $estimatedCost = 0.0,
        public float $realCost = 0.0,
        public ?string $targetCountry = null,
        public ?string $group = null,
        public ?string $content = null,
        public ?string $customerReference = null,
    ) {}
}
