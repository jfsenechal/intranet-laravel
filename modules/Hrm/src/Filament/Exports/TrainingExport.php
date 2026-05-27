<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Training;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class TrainingExport
{
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Agent',
            'Intitulé',
            'Type',
            'Début',
            'Fin',
            'Durée',
            'Attestation',
            'Clôturée',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Training $row): array
    {
        return [
            mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            $row->name,
            $row->training_type?->getLabel(),
            $row->start_date?->format('d/m/Y'),
            $row->end_date?->format('d/m/Y'),
            Training::formatDuration($row->duration_minutes),
            $row->certificate_received ? 'Oui' : 'Non',
            $row->is_closed ? 'Oui' : 'Non',
        ];
    }

    public function downloadXlsx(string $filename): StreamedResponse
    {
        return new StreamedResponse(function (): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $bold = (new Style())->setFontBold();
            $writer->addRow(Row::fromValues($this->headings(), $bold));

            $this->query->with(['employee'])
                ->lazy()
                ->each(function (Training $training) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($training)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
