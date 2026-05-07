<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\CSV\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class EmployeeExport
{
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nom',
            'Prenom',
            'Fonction',
            'Statut',
            'Entree',
            'Email',
            'Archive',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Employee $row): array
    {
        return [
            $row->last_name,
            $row->first_name,
            $row->job_title,
            $row->status?->getLabel(),
            $row->hired_at?->format('d/m/Y'),
            $row->private_email,
            $row->is_archived ? 'Oui' : 'Non',
        ];
    }

    public function downloadCsv(string $filename): StreamedResponse
    {
        return new StreamedResponse(function (): void {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($this->headings()));

            $this->query->lazy()->each(function (Employee $employee) use ($writer): void {
                $writer->addRow(Row::fromValues($this->map($employee)));
            });

            $writer->close();
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
