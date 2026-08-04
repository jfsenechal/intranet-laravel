<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Console\Commands;

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Search\AttachmentOcr;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Override;

/**
 * Detects attachments whose stored `path` no longer matches a file on disk.
 *
 * The legacy "indicateur" PDFs are regenerated with a fresh unique filename,
 * but the `attachments` row keeps pointing at the previous name. This command
 * reports every attachment whose `path` is missing and, when a single other
 * file lives in the same directory, surfaces it as the likely replacement.
 *
 * With `--ocr` it also reports attachments whose cached OCR text is absent or
 * stale, i.e. the ones the Meilisearch indexer would have to re-extract.
 */
final class CheckAttachmentFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    #[Override]
    protected $signature = 'courrier:check-attachment-files
        {--id= : Check only the attachment with the given id}
        {--ocr : Also report attachments that have no cached OCR text file}';

    /**
     * The console command description.
     *
     * @var string
     */
    #[Override]
    protected $description = 'Detect attachments whose stored path does not match a file on disk';

    public function handle(): int
    {
        $disk = Storage::disk(config('courrier.storage.disk'));

        $query = Attachment::query()->whereNotNull('path');

        if ($this->option('id') !== null) {
            $query->whereKey((int) $this->option('id'));
        }

        $checkOcr = (bool) $this->option('ocr');

        $checked = 0;
        $mismatched = 0;
        $missingOcr = 0;

        $query->chunkById(500, function (Collection $attachments) use ($disk, $checkOcr, &$checked, &$mismatched, &$missingOcr): void {
            foreach ($attachments as $attachment) {
                $checked++;

                if (! $disk->exists($attachment->path)) {
                    $mismatched++;
                    $this->reportMissing($disk, $attachment);

                    continue;
                }

                if ($checkOcr && ! $this->hasOcrText($disk, $attachment)) {
                    $missingOcr++;
                }
            }
        });

        $this->newLine();
        $this->info(sprintf('Checked %d attachments, %d mismatched.', $checked, $mismatched));

        if ($checkOcr) {
            $this->info(sprintf('%d attachments without OCR text.', $missingOcr));
        }

        return $mismatched === 0 && $missingOcr === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * A cached OCR file counts only when it is newer than the source file,
     * matching how {@see AttachmentOcr::textFor()} decides to reuse it.
     */
    private function hasOcrText(Filesystem $disk, Attachment $attachment): bool
    {
        $cachePath = AttachmentOcr::cachePathFor($attachment);

        if (! $disk->exists($cachePath)) {
            $this->warn(sprintf(
                'attachment %d (mail %d): no OCR text at "%s"',
                $attachment->id,
                $attachment->incoming_mail_id,
                $cachePath,
            ));

            return false;
        }

        if ($disk->lastModified($cachePath) < $disk->lastModified($attachment->path)) {
            $this->warn(sprintf(
                'attachment %d (mail %d): OCR text is older than the file "%s"',
                $attachment->id,
                $attachment->incoming_mail_id,
                basename($attachment->path),
            ));

            return false;
        }

        return true;
    }

    private function reportMissing(Filesystem $disk, Attachment $attachment): void
    {
        $directory = dirname($attachment->path);
        $onDisk = array_map('basename', $disk->files($directory));

        $hint = match (count($onDisk)) {
            0 => 'directory empty or missing',
            1 => 'actual file: '.$onDisk[0],
            default => 'candidates: '.implode(', ', $onDisk),
        };

        $this->warn(sprintf(
            'attachment %d (mail %d): DB "%s" — %s',
            $attachment->id,
            $attachment->incoming_mail_id,
            basename($attachment->path),
            $hint,
        ));
    }
}
