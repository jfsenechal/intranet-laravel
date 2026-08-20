<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

/**
 * Services and recipients a mail is likely to be routed to, read off the
 * courriers already encoded that resemble it.
 *
 * Both lists are ordered best-first and are only ever suggestions: measured
 * over 2026 mail, the top candidate is the one the mail room actually chose
 * about 43% of the time and the top five contain it about two thirds of the
 * time. That is worth putting in front of a 275-entry select, and nowhere near
 * enough to fill the field.
 */
final readonly class RoutingSuggestion
{
    /**
     * @param  list<int>  $recipientIds
     * @param  list<int>  $serviceIds
     */
    public function __construct(
        public array $recipientIds = [],
        public array $serviceIds = [],
    ) {}

    public static function empty(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->recipientIds === [] && $this->serviceIds === [];
    }
}
