<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Diploma;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DiplomaExport
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
            'Fichier',
            'Ajouté par',
            'Créé le',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Diploma $row): array
    {
        return [
            mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            $row->name,
            $row->certificate_file ? 'Oui' : 'Non',
            $row->user_add,
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
                ->each(function (Diploma $diploma) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($diploma)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
