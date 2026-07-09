<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Models\Recipient;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Override;

final class RecipientDedupeCommand extends Command
{
    private const string CONNECTION = 'maria-courrier';

    #[Override]
    protected $signature = 'courrier:recipients:dedupe {--dry-run : Report duplicates without modifying data}';

    #[Override]
    protected $description = 'Merge duplicate recipients sharing the same username and add a unique index on username';

    private bool $dryRun = false;

    private int $removed = 0;

    private int $repointed = 0;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('Running in DRY-RUN mode - no data will be changed.');
        }

        $usernames = $this->duplicateUsernames();

        if ($usernames->isEmpty()) {
            $this->info('No duplicate usernames found.');
        } else {
            $this->info("Found {$usernames->count()} usernames with duplicates.");

            $dedupe = function () use ($usernames): void {
                $usernames->each(fn (string $username): int => $this->dedupeUsername($username));
            };

            // Wrap in a transaction so a failure leaves no partial merge behind,
            // unless the connection is already inside one (e.g. under test).
            if ($this->dryRun || DB::connection(self::CONNECTION)->getPdo()->inTransaction()) {
                $dedupe();
            } else {
                DB::connection(self::CONNECTION)->transaction($dedupe);
            }

            $this->info("Removed {$this->removed} duplicate recipients, repointed {$this->repointed} references.");
        }

        $this->ensureUniqueUsernameIndex();

        $this->info('Done.');

        return self::SUCCESS;
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

        $mergedEmail = $group
            ->pluck('email')
            ->first(fn (?string $email): bool => filled($email) && $email !== 'noemail@marche.be')
            ?? $canonical->email;

        $mergedSupervisor = $canonical->supervisor_id
            ?? $group->pluck('supervisor_id')->first(fn (?int $id): bool => filled($id));

        $this->line("  {$username}: keeping #{$canonical->id}, merging ".$duplicates->count().' duplicate(s)');

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

    private function ensureUniqueUsernameIndex(): void
    {
        if ($this->hasUniqueUsernameIndex()) {
            $this->info('Unique index on username already present.');

            return;
        }

        if ($this->dryRun) {
            $this->warn('Would add a unique index on recipients.username.');

            return;
        }

        Schema::connection(self::CONNECTION)->table('recipients', function (Blueprint $table): void {
            $table->unique('username');
        });

        $this->info('Added unique index on recipients.username.');
    }

    private function hasUniqueUsernameIndex(): bool
    {
        return collect(Schema::connection(self::CONNECTION)->getIndexes('recipients'))
            ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['username']);
    }
}
