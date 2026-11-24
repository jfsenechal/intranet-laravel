<?php

declare(strict_types=1);

namespace AcMarche\Ad\Ai;

use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

use function in_array;

/**
 * Runs a classified ad through {@see ClassifiedAdModerationAgent}.
 *
 * Images and PDFs are sent along with the text so the model can check them
 * too; other formats cannot be read by the model and only their file name is
 * checked.
 *
 * When the provider is unreachable the ad is let through and the failure is
 * reported: an outage must not stop staff from publishing.
 */
final readonly class ClassifiedAdModerator
{
    private const string DISK = 'public';

    private const array IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    private const string PDF_MIME_TYPE = 'application/pdf';

    /**
     * @param  list<string>  $mediaPaths  attachment paths on the public disk
     */
    public function moderate(string $title, string $htmlContent, array $mediaPaths = []): ModerationVerdict
    {
        $attachments = [];

        foreach ($mediaPaths as $path) {
            $mime = Storage::disk(self::DISK)->mimeType($path) ?: '';

            if (in_array($mime, self::IMAGE_MIME_TYPES, true)) {
                $attachments[] = Files\Image::fromStorage($path, disk: self::DISK);
            } elseif ($mime === self::PDF_MIME_TYPE) {
                $attachments[] = Files\Document::fromStorage($path, disk: self::DISK);
            }
        }

        try {
            $response = ClassifiedAdModerationAgent::make()->prompt(
                $this->prompt($title, $htmlContent, $mediaPaths),
                attachments: $attachments,
            );
        } catch (Throwable $throwable) {
            report($throwable);

            return new ModerationVerdict(isCompliant: true);
        }

        if (! $response instanceof StructuredAgentResponse) {
            return new ModerationVerdict(isCompliant: true);
        }

        return new ModerationVerdict(
            isCompliant: (bool) $response['compliant'],
            reasons: array_values(array_filter((array) $response['reasons'], is_string(...))),
        );
    }

    /**
     * @param  list<string>  $mediaPaths
     */
    private function prompt(string $title, string $htmlContent, array $mediaPaths): string
    {
        $text = mb_trim(html_entity_decode(strip_tags($htmlContent)));
        $fileNames = implode(', ', array_map(basename(...), $mediaPaths));

        return "Vérifie cette petite annonce.\n\n"
            ."Titre : {$title}\n\n"
            ."Texte :\n{$text}\n\n"
            .'Pièces jointes : '.($fileNames !== '' ? $fileNames : 'aucune');
    }
}
