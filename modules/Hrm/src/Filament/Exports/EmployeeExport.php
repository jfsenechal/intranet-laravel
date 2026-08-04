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
     * @param  list<string>  $columns  Selected column keys; empty = all.
     */
    public function __construct(private Builder $query, private array $columns = []) {}

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
        $labels = self::columns();

        return array_map(fn (string $key): string => $labels[$key], $this->selectedColumns());
    }

    /**
     * @return list<null|string>
     */
    public function map(Employee $row): array
    {
        $data = $this->row($row);

        return array_map(fn (string $key) => $data[$key], $this->selectedColumns());
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

    /**
     * @return list<string>
     */
    private function selectedColumns(): array
    {
        $all = array_keys(self::columns());
        if ($this->columns === []) {
            return $all;
        }

        return array_values(array_filter($all, fn (string $key): bool => in_array($key, $this->columns, true)));
    }

    /**
     * @return array<string, null|string>
     */
    private function row(Employee $row): array
    {
        return [
            'last_name' => $row->last_name,
            'first_name' => $row->first_name,
            'job_title' => $row->job_title,
            'status' => $row->status?->getLabel(),
            'hired_at' => $row->hired_at?->format('d/m/Y'),
            'private_email' => $row->private_email,
            'is_archived' => $row->is_archived ? 'Oui' : 'Non',
        ];
    }
}
