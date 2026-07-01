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

        $cachePath = config('courrier.storage.directory').'/ocr/'.$attachment->id.'.txt';
        if ($disk->exists($cachePath) && $disk->lastModified($cachePath) >= $disk->lastModified($relativePath)) {
            return (string) $disk->get($cachePath);
        }

        $text = $this->extract($disk->path($relativePath), (string) $attachment->mime);

        $disk->put($cachePath, $text);

        return $text;
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
        if (! $this->hasBinary('pdftoppm') || ! $this->hasBinary('tesseract')) {
            return '';
        }

        $prefix = tempnam(sys_get_temp_dir(), 'courrier_ocr_');
        if ($prefix === false) {
            return '';
        }
        @unlink($prefix);

        $this->run([
            'pdftoppm', '-png', '-r', (string) $this->dpi, '-l', (string) $this->maxPages, $path, $prefix,
        ]);

        $pages = glob($prefix.'*.png') ?: [];
        sort($pages);

        $text = '';
        foreach ($pages as $page) {
            $text .= ' '.$this->runTesseract($page);
            @unlink($page);
        }

        return mb_trim($text);
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
