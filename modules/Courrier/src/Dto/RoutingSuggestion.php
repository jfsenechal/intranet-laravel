<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Dto;

/**
 * Services and recipients a mail is likely to be routed to, read off the
 * courriers already encoded that resemble it.
 *
 * Both lists are ordered best-first and hold up to five candidates. Only the
 * first {@see self::WRITTEN} of each are ever written into a courrier: measured
 * over 2026 mail, the top candidate is the one the mail room actually chose
 * about 43% of the time for recipients and 50% for services, and the ones
 * further down the ranking are alternatives rather than extra destinations.
 */
final readonly class RoutingSuggestion
{
    /**
     * Candidates per field that are proposed in the courrier itself. The rest
     * of the ranking is kept for callers that want to reason about it, but the
     * form and the batch never fill more than this — every extra row is one the
     * user has to delete while verifying.
     */
    public const int WRITTEN = 2;

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

    /**
     * @return list<int>
     */
    public function topRecipientIds(): array
    {
        return array_slice($this->recipientIds, 0, self::WRITTEN);
    }

    /**
     * @return list<int>
     */
    public function topServiceIds(): array
    {
        return array_slice($this->serviceIds, 0, self::WRITTEN);
    }
}
