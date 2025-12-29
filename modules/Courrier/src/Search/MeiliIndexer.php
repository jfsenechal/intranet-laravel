<?php

namespace AcMarche\Courrier\Search;

use AcMarche\App\Meilisearch\MeiliServer;
use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Models\IncomingMail;
use DateTimeInterface;
use Illuminate\Support\Carbon;

use function chr;

final class MeiliIndexer
{
    use MeiliTrait;

    private string $primaryKey = 'id';

    public static function cleandata($data): string
    {
        $data = preg_replace('#&nbsp;#', ' ', (string) $data);
        $data = preg_replace('#&amp;#', ' ', $data); // &
        $data = preg_replace('#&#', ' ', $data);
        $data = preg_replace('#<#', '', $data);
        $data = preg_replace('#’#', "'", $data);
        $data = preg_replace(["#\(#", "#\)#"], '', $data);
        $special_chars = [
            '?',
            '[',
            ']',
            '/',
            '\\',
            '=',
            '<',
            '>',
            ':',
            ';',
            ',',
            '"',
            '&',
            '$',
            '#',
            '*',
            '|',
            '~',
            '`',
            '!',
            '{',
            '}',
            chr(0),
        ];
        $data = str_replace($special_chars, ' ', $data);

        return mb_trim($data);
    }

    public function addCourrier(IncomingMail $incomingMail): void
    {
        $document = $this->createDocument($incomingMail);
        $this->init(config('courrier.meilisearch.index_name'));
        $index = $this->client->index($this->indexName);
        $index->addDocuments([$document], $this->primaryKey);
    }

    public function addCourriersByYear(int $year): void
    {
        $this->init(config('courrier.meilisearch.index_name'));
        $documents = [];
        foreach ($this->courrierRepository->getByYear($year) as $courrier) {
            $documents[] = $this->createDocument($courrier);
        }
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function addCourriersByDate(DateTimeInterface $date): void
    {
        $this->init(config('courrier.meilisearch.index_name'));
        $documents = [];
        foreach ($this->courrierRepository->findByDateCourrier($date) as $courrier) {
            $documents[] = $this->createDocument($courrier);
        }
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function createDocument(IncomingMail $courrier): array
    {
        $destinatairesId = $servicesId = $original = $copie = [];
        $courrierDestinataires = $this->courrierDestinataireRepository->findByCourrier($courrier);
        foreach ($courrierDestinataires as $courrierDestinataire) {
            $destinataire = $courrierDestinataire->getDestinataire();
            $nom = $destinataire->getNom().' '.$destinataire->getPrenom();
            if ($courrierDestinataire->principal) {
                $original[] = $nom;
            } else {
                $copie[] = $nom;
            }
            $destinatairesId[] = $destinataire->getId();
        }
        $courrierServices = $this->courrierServiceRepository->findByCourrier($courrier);
        foreach ($courrierServices as $courrierService) {
            $service = $courrierService->getService();
            if ($courrierService->principal) {
                $original[] = $service->getNom();
            } else {
                $copie[] = $service->getNom();
            }
            $servicesId[] = $service->getId();
        }
        $document = [];
        $document['id'] = $courrier->getId();
        $document['idSearch'] = MeiliServer::createKey($courrier->getId());
        $document['numero'] = $courrier->numero;
        $document['description'] = $this->cleandata($courrier->description);
        $document['expediteur'] = $this->cleandata($courrier->expediteur);
        $document['destinataires'] = $destinatairesId;
        $document['services'] = $servicesId;
        $document['original'] = $original; // pour affichage
        $document['copie'] = $copie; // pour affichage
        $document['recommande'] = $courrier->recommande;
        $document['date_courrier'] = $courrier->date_courrier->format('Y-m-d');
        $date = $courrier->date_courrier;
        $dateCourrier = Carbon::createFromDate(
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            'UTC',
        )->hour(0)->minute(0)->second(0);
        $document['date_courrier_timestamp'] = $dateCourrier->getTimestamp();
        $content = '';
        $ocrFile = $this->ocr->ocrFile($courrier);
        if (file_exists($ocrFile)) {
            $content = $this->cleandata(file_get_contents($ocrFile));
        }
        $document['content'] = $content;

        return $document;
    }

    public function updateCourrier(IncomingMail $courrier): void
    {
        $this->init(config('courrier.meilisearch.index_name'));
        $documents = [$this->createDocument($courrier)];
        $index = $this->client->index($this->indexName);
        $index->addDocuments($documents, $this->primaryKey);
    }

    public function deleteCourrier(string $id): void
    {
        $this->init(config('courrier.meilisearch.index_name'));
        $index = $this->client->index($this->indexName);
        $index->deleteDocument(MeiliServer::createKey($id));
    }

    private static function createKey(int $id): string
    {
        return 'courrier-'.$id;
    }
}
