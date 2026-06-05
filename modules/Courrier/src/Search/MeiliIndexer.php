<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Models\IncomingMail;

use function chr;

final class MeiliIndexer
{
    use MeiliTrait;

    private const PRIMARY_KEY = 'id';

    private readonly AttachmentOcr $ocr;

    public function __construct(?AttachmentOcr $ocr = null)
    {
        $this->ocr = $ocr ?? new AttachmentOcr();
        $this->init(config('courrier.meilisearch.index_name'));
    }

    public static function cleandata(?string $data): string
    {
        $data = preg_replace('#&nbsp;#', ' ', (string) $data);
        $data = preg_replace('#&amp;#', ' ', (string) $data);
        $data = preg_replace('#&#', ' ', (string) $data);
        $data = preg_replace('#’#', "'", (string) $data);
        $special_chars = [
            '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', '"',
            '&', '$', '#', '*', '|', '~', '`', '!', '{', '}', '(', ')', chr(0),
        ];
        $data = str_replace($special_chars, ' ', (string) $data);

        return mb_trim((string) preg_replace('#\s+#', ' ', $data));
    }

    public function indexMail(IncomingMail $incomingMail): void
    {
        $this->client->index($this->indexName)
            ->addDocuments([$this->createDocument($incomingMail)], self::PRIMARY_KEY);
    }

    /**
     * @param  iterable<IncomingMail>  $incomingMails
     */
    public function indexMails(iterable $incomingMails): void
    {
        $documents = [];
        foreach ($incomingMails as $incomingMail) {
            $documents[] = $this->createDocument($incomingMail);
        }

        if ($documents !== []) {
            $this->client->index($this->indexName)->addDocuments($documents, self::PRIMARY_KEY);
        }
    }

    public function deleteMail(int $id): void
    {
        $this->client->index($this->indexName)->deleteDocument($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function createDocument(IncomingMail $incomingMail): array
    {
        $incomingMail->loadMissing(['recipients', 'services', 'attachments']);

        $original = [];
        $copie = [];

        foreach ($incomingMail->recipients as $recipient) {
            if ($recipient->pivot->is_primary) {
                $original[] = $recipient->full_name;
            } else {
                $copie[] = $recipient->full_name;
            }
        }

        foreach ($incomingMail->services as $service) {
            if ($service->pivot->is_primary) {
                $original[] = $service->name;
            } else {
                $copie[] = $service->name;
            }
        }

        return [
            'id' => $incomingMail->id,
            'numero' => $incomingMail->reference_number,
            'expediteur' => self::cleandata($incomingMail->sender),
            'description' => self::cleandata($incomingMail->description),
            'destinataires' => $incomingMail->recipients->pluck('id')->all(),
            'services' => $incomingMail->services->pluck('id')->all(),
            'original' => $original,
            'copie' => $copie,
            'recommande' => (bool) $incomingMail->is_registered,
            'date_courrier' => $incomingMail->mail_date?->format('Y-m-d'),
            'date_courrier_timestamp' => $incomingMail->mail_date?->getTimestamp(),
            'content' => $this->attachmentsText($incomingMail),
        ];
    }

    /**
     * Concatenate the OCR/extracted text of every attachment.
     */
    private function attachmentsText(IncomingMail $incomingMail): string
    {
        $texts = [];
        foreach ($incomingMail->attachments as $attachment) {
            $text = $this->ocr->textFor($attachment);
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return self::cleandata(implode(' ', $texts));
    }
}
