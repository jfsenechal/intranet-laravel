<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\Courrier\Dto\RoutingSuggestion;
use AcMarche\Courrier\Models\IncomingMail;
use DateTimeInterface;

/**
 * Suggests where an incoming mail should be routed.
 *
 * The seam exists so the form can be tested without a running Meilisearch;
 * {@see SimilarMailFinder} is the only implementation.
 */
interface SuggestsMailRouting
{
    public function suggestFor(IncomingMail $incomingMail): RoutingSuggestion;

    /**
     * Suggest a routing for a mail that has no record yet, from the text just
     * extracted from its document.
     */
    public function suggest(
        string $content,
        string $sender = '',
        ?string $department = null,
        ?int $excludeId = null,
        ?DateTimeInterface $before = null,
    ): RoutingSuggestion;
}
