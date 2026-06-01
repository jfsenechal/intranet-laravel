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
            'last_name' => 'Nom',
            'first_name' => 'Prenom',
            'job_title' => 'Fonction',
            'status' => 'Statut',
            'hired_at' => 'Entree',
            'private_email' => 'Email',
            'is_archived' => 'Archive',
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
    public function map(Employee $row): array
    {
        $values = [
            'last_name' => $row->last_name,
            'first_name' => $row->first_name,
            'job_title' => $row->job_title,
            'status' => $row->status?->getLabel(),
            'hired_at' => $row->hired_at?->format('d/m/Y'),
            'private_email' => $row->private_email,
            'is_archived' => $row->is_archived ? 'Oui' : 'Non',
        ];

        return array_values(array_map(
            fn (string $key) => $values[$key],
            $this->columns,
        ));
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
