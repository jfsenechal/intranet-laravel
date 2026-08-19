<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Ai;

use AcMarche\Courrier\Dto\MailSuggestion;
use AcMarche\Courrier\Search\AttachmentOcr;
use Laravel\Ai\Files;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

use function in_array;
use function sprintf;

/**
 * Analyses an incoming mail document and returns the fields to prefill in the
 * form.
 *
 * Text is extracted locally first: it is far cheaper than sending the whole
 * file, and the existing pdftotext/Tesseract pipeline already handles both
 * native PDFs and scans.
 *
 * A picture of the first page always travels with that text. The reference
 * number comes from the reception stamp inked on the paper before scanning,
 * and that stamp — slanted, pale, next to a handwritten initial — is exactly
 * what text extraction turns into noise or drops, so the model has to see the
 * page to read it. Attaching the PDF instead does not work: it reaches the
 * model as extracted text, missing the stamp just the same.
 */
final readonly class IncomingMailAnalyzer
{
    /**
     * Past this many characters the tail of a document adds signature blocks and
     * boilerplate rather than the object of the mail, so it is dropped.
     */
    private const int MAX_TEXT_LENGTH = 20000;

    private const array IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif'];

    public function __construct(private AttachmentOcr $ocr) {}

    /**
     * @throws RuntimeException when the file cannot be read at all
     */
    public function analyze(string $absolutePath, string $mime = ''): MailSuggestion
    {
        if (! is_file($absolutePath)) {
            throw new RuntimeException(sprintf('Le fichier [%s] est introuvable.', $absolutePath));
        }

        $text = mb_trim($this->ocr->textForPath($absolutePath, $mime));

        $renderedPage = null;

        try {
            [$attachments, $renderedPage] = $this->attachmentsFor($absolutePath, $mime, $text);

            $response = IncomingMailAgent::make()->prompt(
                $text !== '' ? $this->promptForText($text) : 'Analyse le courrier joint.',
                attachments: $attachments,
            );
        } finally {
            if ($renderedPage !== null) {
                @unlink($renderedPage);
            }
        }

        if (! $response instanceof StructuredAgentResponse) {
            throw new RuntimeException("Le modèle n'a pas renvoyé de réponse structurée.");
        }

        return MailSuggestion::fromStructuredResponse($response->toArray());
    }

    /**
     * What the model gets to look at, and the rendered file the caller has to
     * delete afterwards when there is one.
     *
     * A PDF has its first page rasterised: that is where the reception stamp
     * goes, and the PDF itself would only reach the model as text. Images are
     * sent as they are. When nothing can be rendered, the file is handed over
     * whole, which at least keeps a document readable that yielded no text.
     *
     * @return array{0: array<int, Files\File>, 1: string|null}
     *
     * @throws RuntimeException when the format can be neither read locally nor
     *                          sent to the model
     */
    private function attachmentsFor(string $absolutePath, string $mime, string $text): array
    {
        if ($this->isPdf($absolutePath, $mime)) {
            $page = $this->ocr->renderFirstPage($absolutePath);

            if ($page !== null) {
                return [[Files\Image::fromPath($page, 'image/png')], $page];
            }

            return [[Files\Document::fromPath($absolutePath)], null];
        }

        if (in_array($mime, self::IMAGE_MIME_TYPES, true)) {
            return [[Files\Image::fromPath($absolutePath, $mime)], null];
        }

        if ($text !== '') {
            return [[], null];
        }

        throw new RuntimeException(
            sprintf('Aucun texte n\'a pu être extrait de ce document (%s).', $mime !== '' ? $mime : 'format inconnu')
        );
    }

    /**
     * The image is the document of record; the text is only a transcription,
     * and the model has to know which of the two to believe.
     */
    private function promptForText(string $text): string
    {
        return "Analyse le courrier joint : l'image est la première page du document. Le texte ".
            "ci-dessous en est une transcription automatique de l'ensemble des pages, à titre ".
            'indicatif : le cachet de réception peut y manquer ou y être déformé, lis-le sur '.
            "l'image.\n\n".mb_substr($text, 0, self::MAX_TEXT_LENGTH);
    }

    private function isPdf(string $absolutePath, string $mime): bool
    {
        return $mime === 'application/pdf'
            || mb_strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION)) === 'pdf';
    }
}
