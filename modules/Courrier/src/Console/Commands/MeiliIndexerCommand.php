<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\App\Meilisearch\MeiliServer;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Search\MeiliIndexer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Override;

final class MeiliIndexerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'courrier:meili-indexer
        {--fresh : Recreate the index and reapply its settings before indexing}
        {--create-key : Create a scoped Meilisearch API key for the index and exit}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Index every incoming mail in Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $indexName = config('courrier.meilisearch.index_name');
        $server = new MeiliServer($indexName);

        if ($this->option('create-key')) {
            $apiKey = $server->createApiKey();

            $this->info(sprintf('API key created for index "%s":', $indexName));
            $this->line($apiKey->getKey());

            return self::SUCCESS;
        }

        if ($this->option('fresh')) {
            $server->createIndex($indexName, 'id');
            $server->settings(
                config('courrier.meilisearch.filterable_attributes'),
                config('courrier.meilisearch.sortable_attributes'),
            );
            $this->info(sprintf('Index "%s" recreated and configured.', $indexName));
        }

        $indexer = new MeiliIndexer();
        $count = 0;

        IncomingMail::query()
            ->withoutGlobalScopes()
            ->with(['recipients', 'services'])
            ->chunkById(500, function (Collection $incomingMails) use ($indexer, &$count): void {
                $indexer->indexMails($incomingMails);
                $count += $incomingMails->count();
                $this->info(sprintf('Indexed %d incoming mails…', $count));
            });

        $this->info(sprintf('Done. %d incoming mails indexed.', $count));

        return self::SUCCESS;
    }
}
