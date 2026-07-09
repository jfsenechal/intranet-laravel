<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Handler;

use AcMarche\Courrier\Models\Recipient;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Merges recipients that share the same username onto a single canonical row
 * and repoints every reference (supervisor pointers and pivot tables) so the
 * `username` column can be made unique.
 */
final class RecipientDeduplicator
{
    public const string CONNECTION = 'maria-courrier';

    private int $removed = 0;

    private int $repointed = 0;

    /**
     * @param  Closure(string $username, int $canonicalId, int $duplicateCount): void|null  $reporter
     *                                                                                                 Invoked once per duplicated username, before the merge is applied.
     */
    public function __construct(
        private readonly bool $dryRun = false,
        private readonly ?Closure $reporter = null,
    ) {}

    public static function hasUniqueUsernameIndex(): bool
    {
        return collect(Schema::connection(self::CONNECTION)->getIndexes('recipients'))
            ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['username']);
    }

    public static function addUniqueUsernameIndex(): void
    {
        if (self::hasUniqueUsernameIndex()) {
            return;
        }

        Schema::connection(self::CONNECTION)->table('recipients', function (Blueprint $table): void {
            $table->unique('username');
        });
    }

    /**
     * @return array{removed: int, repointed: int}
     */
    public function run(): array
    {
        $connection = DB::connection(self::CONNECTION);

        $work = function (): void {
            $this->duplicateUsernames()->each(fn (string $username): int => $this->dedupeUsername($username));
        };

        // Wrap in a transaction so a failure leaves no partial merge behind,
        // unless the connection is already inside one (e.g. under test).
        if ($this->dryRun || $connection->getPdo()->inTransaction()) {
            $work();
        } else {
            $connection->transaction($work);
        }

        return ['removed' => $this->removed, 'repointed' => $this->repointed];
    }

    /**
     * @return Collection<int, string>
     */
    private function duplicateUsernames(): Collection
    {
        return Recipient::query()
            ->select('username')
            ->groupBy('username')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('username');
    }

    private function dedupeUsername(string $username): int
    {
        /** @var Collection<int, Recipient> $group */
        $group = Recipient::query()
            ->where('username', $username)
            ->orderBy('id')
            ->get();

        // Keep the lowest id as the canonical recipient, then fold the richest
        // data from the duplicates into it (email and attachment preference).
        $canonical = $group->first();
        $duplicates = $group->skip(1);

        if ($this->reporter instanceof Closure) {
            ($this->reporter)($username, $canonical->id, $duplicates->count());
        }

        $mergedEmail = $group
            ->pluck('email')
            ->first(fn (?string $email): bool => filled($email) && $email !== 'noemail@marche.be')
            ?? $canonical->email;

        $mergedSupervisor = $canonical->supervisor_id
            ?? $group->pluck('supervisor_id')->first(fn (?int $id): bool => filled($id));

        foreach ($duplicates as $duplicate) {
            $this->reassignReferences($duplicate->id, $canonical->id);

            if (! $this->dryRun) {
                $duplicate->delete();
            }

            $this->removed++;
        }

        if (! $this->dryRun) {
            $canonical->update([
                'email' => $mergedEmail,
                'supervisor_id' => $mergedSupervisor,
                'receives_attachments' => $group->contains('receives_attachments', true),
            ]);
        }

        return $this->removed;
    }

    /**
     * Repoint every reference to a duplicate recipient onto the canonical one,
     * collapsing pivot rows that would otherwise become duplicated.
     */
    private function reassignReferences(int $duplicateId, int $canonicalId): void
    {
        $connection = DB::connection(self::CONNECTION);

        // Self-referencing supervisor pointers.
        $this->repointed += $this->dryRun
            ? $connection->table('recipients')->where('supervisor_id', $duplicateId)->count()
            : $connection->table('recipients')->where('supervisor_id', $duplicateId)->update(['supervisor_id' => $canonicalId]);

        // incoming_mail_recipient pivot (has its own id and is_primary flag).
        $mailPivots = $connection->table('incoming_mail_recipient')
            ->where('recipient_id', $duplicateId)
            ->get();

        foreach ($mailPivots as $pivot) {
            $existing = $connection->table('incoming_mail_recipient')
                ->where('incoming_mail_id', $pivot->incoming_mail_id)
                ->where('recipient_id', $canonicalId)
                ->first();

            if ($existing !== null) {
                if (! $this->dryRun) {
                    if ($pivot->is_primary && ! $existing->is_primary) {
                        $connection->table('incoming_mail_recipient')
                            ->where('id', $existing->id)
                            ->update(['is_primary' => true]);
                    }

                    $connection->table('incoming_mail_recipient')->where('id', $pivot->id)->delete();
                }
            } elseif (! $this->dryRun) {
                $connection->table('incoming_mail_recipient')
                    ->where('id', $pivot->id)
                    ->update(['recipient_id' => $canonicalId]);
            }

            $this->repointed++;
        }

        // recipient_service pivot (composite primary key, no surrogate id).
        $servicePivots = $connection->table('recipient_service')
            ->where('recipient_id', $duplicateId)
            ->get();

        foreach ($servicePivots as $pivot) {
            $existing = $connection->table('recipient_service')
                ->where('service_id', $pivot->service_id)
                ->where('recipient_id', $canonicalId)
                ->exists();

            if (! $this->dryRun) {
                if ($existing) {
                    $connection->table('recipient_service')
                        ->where('service_id', $pivot->service_id)
                        ->where('recipient_id', $duplicateId)
                        ->delete();
                } else {
                    $connection->table('recipient_service')
                        ->where('service_id', $pivot->service_id)
                        ->where('recipient_id', $duplicateId)
                        ->update(['recipient_id' => $canonicalId]);
                }
            }

            $this->repointed++;
        }
    }
}
