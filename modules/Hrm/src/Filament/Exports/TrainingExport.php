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
    /**
     * @param  list<string>  $columns
     */
    public function __construct(private Builder $query, private array $columns) {}

    /**
     * @return array<string, string>
     */
    public static function columns(): array
    {
        return [
            'employee' => 'Agent',
            'name' => 'Intitulé',
            'training_type' => 'Type',
            'start_date' => 'Début',
            'end_date' => 'Fin',
            'duration_minutes' => 'Durée',
            'certificate_received' => 'Attestation',
            'is_closed' => 'Clôturée',
        ];
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        $columns = self::columns();

        return array_values(array_map(
            fn (string $key): string => $columns[$key],
            $this->columns,
        ));
    }

    /**
     * @return list<null|string>
     */
    public function map(Training $row): array
    {
        $values = [
            'employee' => mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            'name' => $row->name,
            'training_type' => $row->training_type?->getLabel(),
            'start_date' => $row->start_date?->format('d/m/Y'),
            'end_date' => $row->end_date?->format('d/m/Y'),
            'duration_minutes' => Training::formatDuration($row->duration_minutes),
            'certificate_received' => $row->certificate_received ? 'Oui' : 'Non',
            'is_closed' => $row->is_closed ? 'Oui' : 'Non',
        ];

        return array_values(array_map(
            fn (string $key) => $values[$key],
            $this->columns,
        ));
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
