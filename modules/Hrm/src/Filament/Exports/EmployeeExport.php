<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class EmployeeExport
{
    /**
     * @param  Builder<Employee>  $query
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
            'birth_date' => 'Date de naissance',
            'active_functions' => 'Fonction',
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

    public function downloadXlsx(string $filename): StreamedResponse
    {
        return new StreamedResponse(function (): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $bold = (new Style())->setFontBold();
            $writer->addRow(Row::fromValues($this->headings(), $bold));

            $this->rowsQuery()->lazy()->each(function (Employee $employee) use ($writer): void {
                $writer->addRow(Row::fromValues($this->map($employee)));
            });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * The function column is read from the active contracts, so they are eager
     * loaded here. The primary key is appended to the sort to keep the chunks
     * `lazy()` walks deterministic: the table sorts on `last_name`, which is
     * not unique, and ties would otherwise let rows repeat or vanish between
     * two pages.
     *
     * @return Builder<Employee>
     */
    private function rowsQuery(): Builder
    {
        return (clone $this->query)
            ->with('activeContracts')
            ->orderBy(new Employee()->getQualifiedKeyName());
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
            'birth_date' => $row->birth_date?->format('d/m/Y'),
            'active_functions' => $row->activeContracts
                ->pluck('job_title')
                ->filter()
                ->unique()
                ->implode(', '),
            'status' => $row->status?->getLabel(),
            'hired_at' => $row->hired_at?->format('d/m/Y'),
            'private_email' => $row->private_email,
            'is_archived' => $row->is_archived ? 'Oui' : 'Non',
        ];
    }
}
