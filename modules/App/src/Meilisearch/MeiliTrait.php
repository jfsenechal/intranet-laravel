<?php

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
            $this->client = new Client('http://127.0.0.1:7700', $this->masterKey);
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
