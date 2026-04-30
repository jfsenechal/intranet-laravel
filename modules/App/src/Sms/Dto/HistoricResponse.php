<?php

declare(strict_types=1);

namespace AcMarche\App\Sms\Dto;

final class HistoricResponse
{
    /**
     * @param  array<int, HistoricLine>  $lines
     */
    public function __construct(
        public ?string $error = null,
        public array $lines = [],
    ) {}
}
