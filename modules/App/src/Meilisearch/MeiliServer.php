<?php

namespace AcMarche\App\Meilisearch;

use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Endpoints\Keys;

class MeiliServer
{
    use MeiliTrait;

    public function __construct(private readonly string $indexName)
    {
    }

    public static function createKey($id): string
    {
        return self::indexName.'-'.$id;
    }

    /**
     *
     * @return array<'taskUid','indexUid','status','enqueuedAt'>
     */
    public function createIndex(string $indexName, string $primaryKey = 'id'): array
    {
        $this->init($indexName);
        $this->client->deleteTasks((new DeleteTasksQuery())->setStatuses(['failed', 'canceled', 'succeeded']));
        $this->client->deleteIndex($this->indexName);

        return $this->client->createIndex($this->indexName, ['primaryKey' => $primaryKey]);
    }

    /**
     * https://raw.githubusercontent.com/meilisearch/meilisearch/latest/config.toml
     * curl -X PATCH 'http://localhost:7700/experimental-features/' -H 'Content-Type: application/json' -H 'Authorization: Bearer xxxxxx' --data-binary '{"containsFilter": true}'
     * @param array $filterableAttributes
     * @param array $sortableAttributes
     * @return array
     */
    public function settings(array $filterableAttributes, array $sortableAttributes): array
    {
        $this->client->index($this->indexName)->updateFilterableAttributes($filterableAttributes);

        return $this->client->index($this->indexName)->updateSortableAttributes($sortableAttributes);
    }

    public function createApiKey(): Keys
    {
        return $this->client->createKey([
            'description' => $this->indexName.' API key',
            'actions' => ['*'],
            'indexes' => [$this->indexName],
            'expiresAt' => '2042-04-02T00:42:42Z',
        ]);
    }

    public function dump(): array
    {
        return $this->client->createDump();
    }
}
