<?php

declare(strict_types=1);

namespace AcMarche\App\Meilisearch;

use Meilisearch\Client;
use Meilisearch\Endpoints\Indexes;

trait MeiliTrait
{
    public ?Client $client = null;

    private ?Indexes $index = null;

    private ?string $masterKey = null;

    private ?string $indexName = null;

    public function init(string $indexName): void
    {
        $this->setMasterKey(config('app.meilisearch.master_key'));
        $this->indexName = $indexName;

        if (! $this->client) {
            $this->client = new Client(config('app.meilisearch.host'), $this->masterKey);
        }

        if (! $this->index) {
            $this->index = $this->client->index($indexName);
        }
    }

    private function setMasterKey(string $masterKey): void
    {
        $this->masterKey = $masterKey;
    }
}
