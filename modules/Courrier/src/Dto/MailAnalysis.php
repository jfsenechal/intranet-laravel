<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

/**
 * What reading an incoming mail document produced: the fields to prefill, and
 * the text the model was shown.
 *
 * The text travels back with the suggestion because the caller needs it twice
 * over — to look up where comparable mail was routed, and to store on the
 * record so that lookup can be repeated later — and extracting it again would
 * mean a second OCR pass over the same scan.
 */
final readonly class MailAnalysis
{
    public function __construct(
        public MailSuggestion $suggestion,
        public string $documentText,
    ) {}
}
