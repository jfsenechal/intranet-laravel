<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Jobs;

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Search\MeiliIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Keep a single incoming mail in sync with the Meilisearch index.
 *
 * Dispatched after an incoming mail is created so the document (including
 * attachment OCR and relations) is indexed once the request has committed.
 * The nightly `courrier:meili-indexer` command remains the backstop, so a
 * transient Meilisearch outage is logged rather than failing the job.
 */
final class IndexIncomingMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $incomingMailId) {}

    public function handle(): void
    {
        if (blank(config('app.meilisearch.master_key'))) {
            // Meilisearch is not configured (e.g. local or test environment).
            return;
        }

        try {
            // Drop the department restriction (irrelevant in a queue context),
            // but keep soft-delete scoping so trashed mail resolves to null and
            // is removed from the index rather than re-indexed.
            $incomingMail = IncomingMail::query()
                ->withoutGlobalScope(DepartmentScope::class)
                ->with(['recipients', 'services', 'attachments'])
                ->find($this->incomingMailId);

            $indexer = new MeiliIndexer();

            if ($incomingMail === null) {
                $indexer->deleteMail($this->incomingMailId);

                return;
            }

            $indexer->indexMail($incomingMail);
        } catch (Throwable $throwable) {
            report($throwable);
        }
    }
}
