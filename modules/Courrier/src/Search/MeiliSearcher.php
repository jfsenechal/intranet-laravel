<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use DateTimeInterface;

/**
 * Full-text search over indexed incoming mails, enforcing IncomingMailPolicy:
 * administrators see everything, others only mail where they are a recipient
 * or a member of a linked service.
 */
final class MeiliSearcher
{
    use MeiliTrait;

    /**
     * Returned by {@see policyFilter()} when the user may see every document.
     */
    private const NO_RESTRICTION = '';

    public function __construct()
    {
        $this->init(config('courrier.meilisearch.index_name'));
    }

    /**
     * @param  array{date_from?: ?DateTimeInterface, date_to?: ?DateTimeInterface, services?: array<int>, destinataires?: array<int>, recommande?: ?bool, reference?: ?string, sender?: ?string, category?: ?int, department?: ?string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, User $user, array $filters = [], int $limit = 500): array
    {
        $clauses = $this->filterClauses($user, $filters);
        if ($clauses === null) {
            return [];
        }

        $sender = mb_trim((string) ($filters['sender'] ?? ''));
        if ($sender !== '') {
            // `sender` is free text, so it cannot be filtered on directly:
            // resolve it with a search restricted to that attribute, then
            // narrow the main query to the matching ids.
            $ids = self::toIds($this->hits($sender, $clauses, $limit, ['sender']));
            if ($ids === []) {
                return [];
            }

            $clauses[] = sprintf('id IN [%s]', implode(', ', $ids));
        }

        return $this->hits($query, $clauses, $limit);
    }

    /**
     * @param  array{date_from?: ?DateTimeInterface, date_to?: ?DateTimeInterface, services?: array<int>, destinataires?: array<int>, recommande?: ?bool, reference?: ?string, sender?: ?string, category?: ?int, department?: ?string}  $filters
     * @return array<int, int>
     */
    public function searchIds(string $query, User $user, array $filters = [], int $limit = 500): array
    {
        return self::toIds($this->search($query, $user, $filters, $limit));
    }

    /**
     * Ids of the mail the user personally receives, most recent mail date first.
     *
     * Unlike {@see search()} this never widens to the administrator or
     * department visibility: it always answers "my mail".
     *
     * @return array<int, int>
     */
    public function myMailIds(User $user, ?DateTimeInterface $since = null, int $limit = 10): array
    {
        $clause = $this->recipientClause($user);
        if ($clause === null) {
            return [];
        }

        $clauses = [$clause];
        if ($since instanceof DateTimeInterface) {
            $clauses[] = 'mail_date_timestamp >= '.$since->getTimestamp();
        }

        return self::toIds($this->hits('', $clauses, $limit));
    }

    /**
     * Build the policy clause for the given user.
     *
     * Mirrors the listing tiers: administrators may see everything (empty
     * string), index/admin users are scoped to their department(s), and every
     * other user only sees mail they receive or that targets one of their
     * services. Returns `null` when the user may see nothing.
     */
    public function policyFilter(User $user): ?string
    {
        if ($user->isAdministrator()) {
            return self::NO_RESTRICTION;
        }

        $departments = $user->getCourrierViewableDepartments();
        if ($departments !== []) {
            $values = array_map(
                static fn (DepartmentCourrierEnum $department): string => '"'.$department->value.'"',
                $departments,
            );

            return sprintf('department IN [%s]', implode(', ', $values));
        }

        return $this->recipientClause($user);
    }

    /**
     * @param  array<int, array<string, mixed>>  $hits
     * @return array<int, int>
     */
    private static function toIds(array $hits): array
    {
        return array_map(static fn (array $hit): int => (int) $hit['id'], $hits);
    }

    /**
     * Run the query against the index and return its raw hits.
     *
     * @param  array<int, string>  $clauses
     * @param  array<int, string>|null  $attributesToSearchOn  restricts the query to these fields
     * @return array<int, array<string, mixed>>
     */
    private function hits(string $query, array $clauses, int $limit, ?array $attributesToSearchOn = null): array
    {
        $options = [
            'filter' => $clauses,
            'sort' => ['mail_date_timestamp:desc'],
            'limit' => $limit,
        ];

        if ($attributesToSearchOn !== null) {
            $options['attributesToSearchOn'] = $attributesToSearchOn;
        }

        $result = $this->client->index($this->indexName)->search(mb_trim($query), $options);

        return $result->getHits();
    }

    /**
     * Clause matching the mail a user personally receives: addressed to them,
     * or to one of the services they belong to. Returns `null` when the user is
     * not a known recipient.
     */
    private function recipientClause(User $user): ?string
    {
        $recipient = Recipient::query()->where('username', $user->username)->first();
        if ($recipient === null) {
            return null;
        }

        $serviceIds = $recipient->services()->pluck('courrier_services.id')->all();
        if ($serviceIds === []) {
            return sprintf('recipients IN [%d]', $recipient->id);
        }

        return sprintf(
            '(recipients IN [%d] OR services IN [%s])',
            $recipient->id,
            implode(', ', $serviceIds),
        );
    }

    /**
     * Build the clause for the dedicated id / reference-number lookup field.
     *
     * A bare number matches either the incoming-mail id or the reference
     * number; anything else (e.g. "2026-42") matches the reference number only.
     */
    private function referenceClause(string $reference): string
    {
        $reference = mb_trim($reference);
        $escaped = str_replace('"', '\"', $reference);

        if (ctype_digit($reference)) {
            return sprintf('(id = %d OR reference_number = "%s")', (int) $reference, $escaped);
        }

        return sprintf('reference_number = "%s"', $escaped);
    }

    /**
     * Combine the policy clause with the optional user-provided filters.
     *
     * @param  array{date_from?: ?DateTimeInterface, date_to?: ?DateTimeInterface, services?: array<int>, destinataires?: array<int>, recommande?: ?bool, reference?: ?string, sender?: ?string, category?: ?int, department?: ?string}  $filters
     * @return array<int, string>|null null when the user may see nothing
     */
    private function filterClauses(User $user, array $filters): ?array
    {
        $policyClause = $this->policyFilter($user);
        if ($policyClause === null) {
            return null;
        }

        $clauses = [];
        if ($policyClause !== self::NO_RESTRICTION) {
            $clauses[] = $policyClause;
        }

        if (filled($filters['reference'] ?? null)) {
            $clauses[] = $this->referenceClause((string) $filters['reference']);
        }
        if (! empty($filters['category'])) {
            $clauses[] = 'category_id = '.(int) $filters['category'];
        }
        if (! empty($filters['date_from'])) {
            $clauses[] = 'mail_date_timestamp >= '.$filters['date_from']->getTimestamp();
        }
        if (! empty($filters['date_to'])) {
            $clauses[] = 'mail_date_timestamp <= '.$filters['date_to']->getTimestamp();
        }
        if (! empty($filters['services'])) {
            $clauses[] = sprintf('services IN [%s]', implode(', ', array_map('intval', $filters['services'])));
        }
        if (! empty($filters['destinataires'])) {
            $clauses[] = sprintf('recipients IN [%s]', implode(', ', array_map('intval', $filters['destinataires'])));
        }
        if (filled($filters['department'] ?? null)) {
            $escaped = str_replace('"', '\"', (string) $filters['department']);
            $clauses[] = sprintf('department = "%s"', $escaped);
        }
        if (array_key_exists('recommande', $filters) && $filters['recommande'] !== null) {
            $clauses[] = 'is_registered = '.($filters['recommande'] ? 'true' : 'false');
        }

        return $clauses;
    }
}
