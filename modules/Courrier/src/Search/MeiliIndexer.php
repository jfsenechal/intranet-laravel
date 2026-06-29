<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\App\Meilisearch\MeiliTrait;
use AcMarche\Courrier\Models\IncomingMail;

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

    public static function cleanData(?string $data): string
    {
        $data = html_entity_decode((string) $data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $data = strip_tags($data);
        // drop control chars, then collapse whitespace
        $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', ' ', $data);

        return mb_trim((string) preg_replace('#\s+#u', ' ', (string) $data));
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

        $content = self::cleanData($this->attachmentsText($incomingMail));
        $this->persistContent($incomingMail, $content);

        return [
            'id' => $incomingMail->id,
            'reference_number' => $incomingMail->reference_number,
            'sender' => $incomingMail->sender,
            'description' => $incomingMail->description,
            'recipients' => $incomingMail->recipients->pluck('id')->all(),
            'services' => $incomingMail->services->pluck('id')->all(),
            'original' => $original,
            'copie' => $copie,
            'department' => $incomingMail->department,
            'follow_up_note' => $incomingMail->follow_up_note,
            'is_registered' => $incomingMail->is_registered,
            'is_notified' => $incomingMail->is_notified,
            'has_acknowledgment' => $incomingMail->has_acknowledgment,
            'category_id' => $incomingMail->category_id,
            'mail_date' => $incomingMail->mail_date?->format('Y-m-d'),
            'mail_date_timestamp' => $incomingMail->mail_date?->getTimestamp(),
            'content' => $content,
        ];
    }

    /**
     * Store the extracted attachment text on the incoming mail so it is
     * available outside the search index. Persisted quietly to avoid
     * re-dispatching the index job and only when the value changed.
     */
    private function persistContent(IncomingMail $incomingMail, string $content): void
    {
        if (! $incomingMail->exists || $incomingMail->content === $content) {
            return;
        }

        $incomingMail->content = $content;
        $incomingMail->saveQuietly();
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

        return implode(' ', $texts);
    }
}
