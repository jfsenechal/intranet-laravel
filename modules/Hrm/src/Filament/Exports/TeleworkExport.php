<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Telework;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class TeleworkExport
{
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Agent',
            'Nom complet',
            'Lieu',
            'Type de jour',
            'Jour fixe',
            'Validé direction',
            'Validé le',
            'Validation Grh par',
            'Créé le',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Telework $row): array
    {
        $fullName = $row->employee
            ? mb_trim(($row->employee->last_name ?? '').' '.($row->employee->first_name ?? ''))
            : '';

        return [
            $row->user_add,
            $fullName,
            $row->location_type?->getLabel(),
            $row->day_type?->getLabel(),
            $row->fixed_day?->getLabel(),
            $row->manager_validated ? 'Oui' : 'Non',
            $row->manager_validated_at?->format('d/m/Y'),
            $row->hr_validator_name,
            $row->created_at?->format('d/m/Y'),
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
                ->each(function (Telework $telework) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($telework)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
