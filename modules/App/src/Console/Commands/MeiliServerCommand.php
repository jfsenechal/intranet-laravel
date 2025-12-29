<?php

namespace AcMarche\App\Console\Commands;

use AcMarche\App\Meilisearch\MeiliServer;
use AcMarche\App\Meilisearch\MeiliTrait;
use Illuminate\Console\Command;

final class MeiliServerCommand extends Command
{
    use MeiliTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:meili-server {indexName} {--reset : Reset and create index} {--dump : Run without making changes} {--tasks : Run without making changes} {--api : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and reset index';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $indexName = $this->argument('indexName');
        $reset = $this->option('reset');
        $task = $this->option('tasks');
        $dump = $this->option('dump');
        $api = $this->option('api');

        if (! $indexName) {
            $this->error('Index name is required');

            return;
        }

        $meiliServer = new MeiliServer($indexName);
        if ($reset) {
            $meiliServer->createIndex($indexName);
            $meiliServer->settings(
                config($indexName.'meilisearch.filterable_attributes'),
                config($indexName.'meilisearch.sortable_attributes')
            );
            $this->info('Don\'t forget to enable contains filter in the settings');
            $this->info(
                "curl -X PATCH 'http://localhost:7700/experimental-features/' -H 'Content-Type: application/json' -H 'Authorization: Bearer xxxxxx' --data-binary '{\"containsFilter\": true}'"
            );

        }
        if ($task) {
            $this->tasks($indexName);
        }
        if ($dump) {
            $meiliServer->dump();
        }
        if ($api) {
            $meiliServer->createApiKey();
        }

    }

    private function tasks(string $indexName): void
    {
        $meiliServer = new MeiliServer($indexName);
        $this->init(config($indexName.'meilisearch.index_name'));
        $tasks = $meiliServer->client->getTasks();
        $data = [];
        foreach ($tasks->getResults() as $result) {
            $t = [$result['uid'], $result['status'], $result['type'], $result['startedAt']];
            $t['error'] = null;
            $t['url'] = null;
            if ($result['status'] === 'failed') {
                if (isset($result['error'])) {
                    $t['error'] = $result['error']['message'];
                    $t['link'] = $result['error']['link'];
                }
            }
            $data[] = $t;
        }
        $table = new Table($output);
        $table
            ->setHeaders(['Uid', 'status', 'Type', 'Date', 'Error', 'Url'])
            ->setRows($data);
        $table->render();
    }
}
