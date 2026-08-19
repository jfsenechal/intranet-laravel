<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Search;

use AcMarche\Courrier\Models\Attachment;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

use function in_array;

/**
 * Extracts searchable text from incoming-mail attachments.
 *
 * PDFs with a text layer are read with `pdftotext`; scanned PDFs are
 * rasterised with `pdftoppm` and images are read with Tesseract OCR. The
 * extracted text is cached next to the source file so re-indexing is cheap.
 * Every step degrades to an empty string when a binary or file is missing,
 * so indexing never fails because of OCR.
 */
final class AttachmentOcr
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp'];

    private readonly bool $enabled;

    private readonly string $language;

    private readonly int $maxPages;

    private readonly int $dpi;

    private readonly int $timeout;

    /**
     * @var array<string, bool>
     */
    private array $binaries = [];

    public function __construct(?bool $enabled = null)
    {
        $this->enabled = $enabled ?? (bool) config('courrier.ocr.enabled', true);
        $this->language = (string) config('courrier.ocr.language', 'fra');
        $this->maxPages = (int) config('courrier.ocr.max_pages', 15);
        $this->dpi = (int) config('courrier.ocr.dpi', 200);
        $this->timeout = (int) config('courrier.ocr.timeout', 120);
    }

    /**
     * Relative path, on the courrier disk, of the cached extracted text.
     */
    public static function cachePathFor(Attachment $attachment): string
    {
        return config('courrier.storage.directory').'/ocr/'.$attachment->id.'.txt';
    }

    public function textFor(Attachment $attachment): string
    {
        if (! $this->enabled || $attachment->path === null) {
            return '';
        }

        $disk = $this->disk();
        $relativePath = $attachment->path;

        if (! $disk->exists($relativePath)) {
            return '';
        }

        $cachePath = self::cachePathFor($attachment);
        if ($disk->exists($cachePath) && $disk->lastModified($cachePath) >= $disk->lastModified($relativePath)) {
            return (string) $disk->get($cachePath);
        }

        $text = $this->extract($disk->path($relativePath), (string) $attachment->mime);

        $disk->put($cachePath, $text);

        return $text;
    }

    /**
     * Extract text from a file sitting on the local filesystem, bypassing the
     * per-attachment cache. Used by flows holding a file that is not (yet) an
     * Attachment, such as the AI form completion on a pending upload.
     */
    public function textForPath(string $absolutePath, string $mime = ''): string
    {
        if (! $this->enabled) {
            return '';
        }

        return $this->extract($absolutePath, $mime);
    }

    /**
     * Rasterise the first page of a PDF and return the path of the PNG, or null
     * when it cannot be rendered. The caller owns the file and must delete it.
     *
     * Used by flows that need the model to *see* the page rather than read its
     * text, such as the reception stamp the AI completion picks the reference
     * number from.
     */
    public function renderFirstPage(string $absolutePath): ?string
    {
        return $this->rasterize($absolutePath, 1)[0] ?? null;
    }

    private function extract(string $path, string $mime): string
    {
        if (! is_file($path)) {
            return '';
        }

        $extension = mb_strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($mime === 'application/pdf' || $extension === 'pdf') {
            return $this->extractFromPdf($path);
        }

        if (str_starts_with($mime, 'image/') || in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return $this->runTesseract($path);
        }

        // Office documents and other formats are not OCR-able here.
        return '';
    }

    private function extractFromPdf(string $path): string
    {
        $text = $this->runPdfToText($path);
        if (mb_trim($text) !== '') {
            return $text;
        }

        // No embedded text layer: treat it as a scan and OCR each page.
        return $this->ocrScannedPdf($path);
    }

    private function runPdfToText(string $path): string
    {
        if (! $this->hasBinary('pdftotext')) {
            return '';
        }

        return $this->run(['pdftotext', '-q', '-enc', 'UTF-8', $path, '-']);
    }

    private function ocrScannedPdf(string $path): string
    {
        if (! $this->hasBinary('tesseract')) {
            return '';
        }

        $text = '';
        foreach ($this->rasterize($path, $this->maxPages) as $page) {
            $text .= ' '.$this->runTesseract($page);
            @unlink($page);
        }

        return mb_trim($text);
    }

    /**
     * @return array<int, string> paths of the rendered pages, in page order.
     *                            The caller owns them and must delete them.
     */
    private function rasterize(string $path, int $maxPages): array
    {
        if (! $this->hasBinary('pdftoppm')) {
            return [];
        }

        $prefix = tempnam(sys_get_temp_dir(), 'courrier_ocr_');
        if ($prefix === false) {
            return [];
        }
        @unlink($prefix);

        $this->run([
            'pdftoppm', '-png', '-r', (string) $this->dpi, '-l', (string) $maxPages, $path, $prefix,
        ]);

        $pages = glob($prefix.'*.png') ?: [];
        sort($pages);

        return $pages;
    }

    private function runTesseract(string $imagePath): string
    {
        if (! $this->hasBinary('tesseract')) {
            return '';
        }

        return $this->run(['tesseract', $imagePath, 'stdout', '-l', $this->language]);
    }

    /**
     * @param  array<int, string>  $command
     */
    private function run(array $command): string
    {
        try {
            $process = new Process($command);
            $process->setTimeout($this->timeout);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return $process->getOutput();
        } catch (Throwable) {
            return '';
        }
    }

    private function hasBinary(string $name): bool
    {
        return $this->binaries[$name] ??= (new ExecutableFinder())->find($name) !== null;
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('courrier.storage.disk'));
    }
}
