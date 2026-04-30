<?php

declare(strict_types=1);

namespace AcMarche\Pst\Search;

use AcMarche\Pst\Models\Action;
use Meilisearch\Contracts\DeleteTasksQuery;
use Meilisearch\Endpoints\Indexes;
use Meilisearch\Endpoints\Keys;

final class MeiliServer
{
    use MeiliTrait;

    public $courrierRepository;

    public $courrierDestinataireRepository;

    public $courrierServiceRepository;

    public $ocr;

    private string $primaryKey = 'idSearch';

    private ?Indexes $index = null;

    public function __construct(
        private string $indexName,
        private string $masterKey,
    ) {
        $this->indexName = config('pst.meili.index_name');
        $this->masterKey = config('pst.meili.key');
    }

    /**
     * @return array<'taskUid','indexUid','status','enqueuedAt'>
     */
    public function createIndex(): array
    {
        $this->init();
        $this->client->deleteTasks(new DeleteTasksQuery()->setStatuses(['failed', 'canceled', 'succeeded']));
        $this->client->deleteIndex($this->indexName);

        return $this->client->createIndex($this->indexName, ['primaryKey' => $this->primaryKey]);
    }

    /**
     * https://raw.githubusercontent.com/meilisearch/meilisearch/latest/config.toml
     * curl -X PATCH 'http://localhost:7700/experimental-features/' -H 'Content-Type: application/json' -H 'Authorization: Bearer xxxxxx' --data-binary '{"containsFilter": true}'
     */
    public function settings(): array
    {
        $this->client->index($this->indexName)->updateFilterableAttributes($this->filterableAttributes);

        return $this->client->index($this->indexName)->updateSortableAttributes($this->sortableAttributes);
    }

    public function createApiKey(): Keys
    {
        $this->init();

        return $this->client->createKey([
            'description' => 'indicateur ville API key',
            'actions' => ['*'],
            'indexes' => [$this->indexName],
            'expiresAt' => '2042-04-02T00:42:42Z',
        ]);
    }

    public function addAction(Action $action): void
    {
        $document = $this->createDocument($action);
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->addDocuments([$document], $this->primaryKey);
    }

    public function addActionsByYear(int $year): void
    {
        $this->init();
        $documents = [];
        foreach ($this->courrierRepository->getByYear($year) as $action) {
            $documents[] = $this->createDocument($action);
        }
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function addActionsByDate(DateTimeInterface $date): void
    {
        $this->init();
        $documents = [];
        foreach ($this->courrierRepository->findByDateAction($date) as $action) {
            $documents[] = $this->createDocument($action);
        }
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function createDocument(Action $action): array
    {
        $destinatairesId = $servicesId = $original = $copie = [];
        $actionDestinataires = $this->courrierDestinataireRepository->findByAction($action);
        foreach ($actionDestinataires as $actionDestinataire) {
            $destinataire = $actionDestinataire->getDestinataire();
            $nom = $destinataire->getNom().' '.$destinataire->getPrenom();
            if ($actionDestinataire->principal) {
                $original[] = $nom;
            } else {
                $copie[] = $nom;
            }
            $destinatairesId[] = $destinataire->getId();
        }
        $actionServices = $this->courrierServiceRepository->findByAction($action);
        foreach ($actionServices as $actionService) {
            $service = $actionService->getService();
            if ($actionService->principal) {
                $original[] = $service->getNom();
            } else {
                $copie[] = $service->getNom();
            }
            $servicesId[] = $service->getId();
        }
        $document = [];
        $document['id'] = $action->getId();
        $document['idSearch'] = $this->createKey($action->getId());
        $document['numero'] = $action->numero;
        $document['description'] = Cleaner::cleandata($action->description);
        $document['expediteur'] = Cleaner::cleandata($action->expediteur);
        $document['destinataires'] = $destinatairesId;
        $document['services'] = $servicesId;
        $document['original'] = $original; // pour affichage
        $document['copie'] = $copie; // pour affichage
        $document['recommande'] = $action->recommande;
        $document['date_courrier'] = $action->date_courrier->format('Y-m-d');
        $date = $action->date_courrier;
        $dateAction = Carbon::createFromDate(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            'UTC',
        )->hour(0)->minute(0)->second(0);
        $document['date_courrier_timestamp'] = $dateAction->getTimestamp();
        $content = '';
        $ocrFile = $this->ocr->ocrFile($action);
        if (file_exists($ocrFile)) {
            $content = Cleaner::cleandata(file_get_contents($ocrFile));
        }
        $document['content'] = $content;

        return $document;
    }

    public function updateAction(Action $action): void
    {
        $this->init();
        $documents = [$this->createDocument($action)];
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function deleteAction(string $id): void
    {
        $this->init();
        $index = $this->client->index($this->indexName);
        $index->deleteDocument($this->createKey($id));
    }

    public function dump(): array
    {
        $this->init();

        return $this->client->createDump();
    }

    private function createKey(int $id): string
    {
        return 'pst-'.$id;
    }
}
