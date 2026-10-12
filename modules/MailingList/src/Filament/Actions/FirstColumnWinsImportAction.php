<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use Filament\Actions\ImportAction;
use League\Csv\Reader as CsvReader;
use League\Csv\Writer as CsvWriter;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;

/**
 * Import action accepting CSV files with repeated column names, such as
 * Thunderbird exports ("Ville", "Code postal"... for home and work).
 * Only the first column of each name is kept; later ones are dropped.
 */
final class FirstColumnWinsImportAction extends ImportAction
{
    /**
     * @return resource|false
     */
    #[Override]
    public function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        $stream = parent::getUploadedFileStream($file);

        if (! $stream) {
            return $stream;
        }

        $csvReader = CsvReader::from($stream);
        $csvDelimiter = $this->getCsvDelimiter($csvReader);

        if (filled($csvDelimiter)) {
            $csvReader->setDelimiter($csvDelimiter);
        }

        $rows = iterator_to_array($csvReader->getRecords(), false);
        $header = $rows[$this->getHeaderOffset() ?? 0] ?? [];
        $keptIndexes = array_keys(array_unique($header));

        $csvWriter = CsvWriter::fromString();

        if (filled($csvDelimiter)) {
            $csvWriter->setDelimiter($csvDelimiter);
        }

        foreach ($rows as $row) {
            $csvWriter->insertOne(array_map(fn (int $index): string => (string) ($row[$index] ?? ''), $keptIndexes));
        }

        $deduplicatedStream = fopen('php://temp', 'r+');
        fwrite($deduplicatedStream, $csvWriter->toString());
        rewind($deduplicatedStream);

        return $deduplicatedStream;
    }
}
