<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Dto\RoutingSuggestion;
use AcMarche\Courrier\Models\IncomingMail;
use DateTimeInterface;
use Throwable;

use function count;
use function in_array;

/**
 * Suggests where a mail should be routed by looking up the courriers already
 * encoded that read like it.
 *
 * The routing of an incoming mail is not written on the paper: measured over
 * 16.000 links, the surname of the person it was given to appears in the letter
 * only 13% of the time. It is institutional knowledge — the mail room knows
 * which desk handles a planning permit — and the only place that knowledge is
 * written down is the 166.000 courriers already encoded. So the suggestion is
 * retrieved, never reasoned: the letter's text is thrown at the index, and the
 * services and recipients of the mails that come back are tallied.
 *
 * The text is what carries the signal. Measured against the sender alone, which
 * gets the right recipient 21% of the time, the text gets it 40%; adding the
 * sender as a boost on top is worth about two points more. Services behave the
 * other way round — a correspondent maps to a desk — which is why the sender
 * boost is kept even though it does little for recipients.
 */
final class SimilarMailFinder implements SuggestsMailRouting
{
    use MeiliTrait;

    /**
     * Neighbours to read the routing off. Beyond this the hits stop resembling
     * the letter and only add noise to the tally.
     */
    private const int NEIGHBOURS = 30;

    /**
     * Candidates handed back per field. Five is what fits above a select
     * without turning it into a second list to read.
     */
    private const int CANDIDATES = 5;

    /**
     * A hit from the same correspondent counts triple: the same sender writing
     * about the same thing is the strongest neighbour there is.
     */
    private const int SAME_SENDER_WEIGHT = 3;

    /**
     * Query terms taken from the letter. Meilisearch drops the least selective
     * words itself, so this is a ceiling rather than a target.
     */
    private const int QUERY_WORDS = 50;

    /**
     * Words present in nearly every Belgian administrative letter, which match
     * everything and rank nothing.
     */
    private const array NOISE_WORDS = [
        'pour', 'avec', 'dans', 'vous', 'nous', 'votre', 'notre', 'cette', 'elle', 'plus',
        'sont', 'leur', 'entre', 'aussi', 'tout', 'tous', 'ainsi', 'tres', 'très', 'date',
        'madame', 'monsieur', 'messieurs', 'commune', 'communal', 'communale', 'ville',
        'marche', 'famenne', 'courrier', 'lettre', 'veuillez', 'agreer', 'agréer',
        'salutations', 'distinguees', 'distinguées', 'cordialement', 'objet', 'concerne',
        'reference', 'référence', 'telephone', 'téléphone', 'email', 'adresse', 'rue',
        'code', 'postal', 'belgique', 'college', 'collège', 'administration',
    ];

    public function __construct()
    {
        $this->init(config('courrier.meilisearch.index_name'));
    }

    /**
     * Suggest the routing of a mail already encoded, from the text extracted
     * from its document.
     *
     * The mail itself is excluded, and so is everything encoded after it: a
     * suggestion must only ever rest on what was already known.
     */
    public function suggestFor(IncomingMail $incomingMail): RoutingSuggestion
    {
        return $this->suggest(
            (string) $incomingMail->content,
            (string) $incomingMail->sender,
            $incomingMail->department,
            $incomingMail->id,
            $incomingMail->mail_date,
        );
    }

    /**
     * @param  string  $content  text extracted from the document, not the one-line description
     * @param  string|null  $department  restricts the neighbours to one department, since each keeps
     *                                   its own services and its own habits
     */
    public function suggest(
        string $content,
        string $sender = '',
        ?string $department = null,
        ?int $excludeId = null,
        ?DateTimeInterface $before = null,
    ): RoutingSuggestion {
        $query = self::queryFrom($content);

        if ($query === '') {
            return RoutingSuggestion::empty();
        }

        try {
            $hits = $this->neighbours($query, $department, $excludeId, $before);
        } catch (Throwable $throwable) {
            // The index being down must not stop a courrier from being encoded:
            // the suggestion is a convenience, the selects work without it.
            report($throwable);

            return RoutingSuggestion::empty();
        }

        if ($hits === []) {
            return RoutingSuggestion::empty();
        }

        return new RoutingSuggestion(
            self::tally($hits, 'recipients', $sender),
            self::tally($hits, 'services', $sender),
        );
    }

    /**
     * Weighted vote over the neighbours: a hit counts less the further down the
     * ranking it sits, and triple when it comes from the same correspondent.
     *
     * @param  array<int, array<string, mixed>>  $hits
     * @return list<int>
     */
    private static function tally(array $hits, string $field, string $sender): array
    {
        $scores = [];
        $sender = mb_strtolower(mb_trim($sender));

        foreach ($hits as $rank => $hit) {
            $weight = 1 / ($rank + 2);

            if ($sender !== '' && mb_strtolower(mb_trim((string) ($hit['sender'] ?? ''))) === $sender) {
                $weight *= self::SAME_SENDER_WEIGHT;
            }

            foreach ($hit[$field] ?? [] as $id) {
                $id = (int) $id;
                $scores[$id] = ($scores[$id] ?? 0) + $weight;
            }
        }

        arsort($scores);

        return array_slice(array_keys($scores), 0, self::CANDIDATES);
    }

    /**
     * Turn a letter into a query: the distinctive words, deduplicated, with the
     * boilerplate every administrative letter shares thrown away.
     *
     * Numbers are dropped too. A reference or an account number is unique to
     * one letter, so it matches nothing and only costs a slot.
     */
    private static function queryFrom(string $content): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $kept = [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 4 || is_numeric($word) || in_array($word, self::NOISE_WORDS, true)) {
                continue;
            }

            $kept[$word] = true;

            if (count($kept) >= self::QUERY_WORDS) {
                break;
            }
        }

        return implode(' ', array_keys($kept));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function neighbours(string $query, ?string $department, ?int $excludeId, ?DateTimeInterface $before): array
    {
        $filter = [];

        if ($department !== null && $department !== '') {
            $filter[] = 'department = "'.$department.'"';
        }

        if ($excludeId !== null) {
            $filter[] = 'id != '.$excludeId;
        }

        if ($before instanceof DateTimeInterface) {
            $filter[] = 'mail_date_timestamp <= '.$before->getTimestamp();
        }

        $result = $this->client->index($this->indexName)->search($query, [
            'filter' => $filter,
            'limit' => self::NEIGHBOURS,
            // The default strategy drops words from the end of the query, which
            // for a letter means dropping its body and keeping its letterhead.
            // "frequency" drops the least selective words instead.
            'matchingStrategy' => 'frequency',
            'attributesToRetrieve' => ['id', 'sender', 'recipients', 'services'],
        ]);

        return $result->getHits();
    }
}
