<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Deadline;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DeadlineExport
{
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Intitulé',
            'Agent',
            'Employeur',
            'Début',
            'Fin',
            'Rappel',
            'Clôture',
            'Clôturée',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Deadline $row): array
    {
        return [
            $row->name,
            mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            $row->employer?->name,
            $row->start_date?->format('d/m/Y'),
            $row->end_date?->format('d/m/Y'),
            $row->reminder_date?->format('d/m/Y'),
            $row->closed_date?->format('d/m/Y'),
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

            $this->query->with(['employee', 'employer'])
                ->lazy()
                ->each(function (Deadline $deadline) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($deadline)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
