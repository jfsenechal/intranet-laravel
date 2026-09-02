<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Exports;

use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Flattens the directory into a spreadsheet: one row per agent, with the
 * service, direction and function read from the active contracts.
 *
 * An agent can hold several contracts at once, so the three contract columns
 * are repeated as many times as the widest agent in the exported query needs
 * (`Service 2`, `Direction 2`, `Fonction 2`, ...). Agents with fewer contracts
 * leave the extra columns empty, which keeps every row on the same grid.
 */
final readonly class EmployeeDirectoryExport
{
    /**
     * @param  Builder<Employee>  $query
     */
    public function __construct(private Builder $query) {}

    /**
     * The number of contract column groups the export needs, which is the
     * highest active contract count in the query. Always at least one, so a
     * query returning nothing still produces the full header row.
     */
    public function contractSlots(): int
    {
        $widest = (clone $this->query)
            ->withCount('activeContracts')
            ->reorder()
            ->orderByDesc('active_contracts_count')
            ->first();

        return max((int) ($widest->active_contracts_count ?? 0), 1);
    }

    /**
     * @return list<string>
     */
    public function headings(int $contractSlots): array
    {
        $headings = ['Prenom', 'Nom'];

        for ($slot = 1; $slot <= $contractSlots; $slot++) {
            $suffix = $slot > 1 ? ' '.$slot : '';

            $headings[] = 'Service'.$suffix;
            $headings[] = 'Direction'.$suffix;
            $headings[] = 'Fonction'.$suffix;
        }

        return $headings;
    }

    /**
     * @return list<null|string>
     */
    public function map(Employee $employee, int $contractSlots): array
    {
        $values = [$employee->first_name, $employee->last_name];
        $contracts = $employee->activeContracts->values();

        for ($slot = 0; $slot < $contractSlots; $slot++) {
            /** @var Contract|null $contract */
            $contract = $contracts->get($slot);

            $values[] = $contract?->service?->name;
            $values[] = $contract?->direction?->name;
            $values[] = $contract?->job_title;
        }

        return $values;
    }

    public function downloadXlsx(string $filename): StreamedResponse
    {
        $contractSlots = $this->contractSlots();

        return new StreamedResponse(function () use ($contractSlots): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $bold = (new Style())->setFontBold();
            $writer->addRow(Row::fromValues($this->headings($contractSlots), $bold));

            $this->rowsQuery()->lazy()->each(function (Employee $employee) use ($writer, $contractSlots): void {
                $writer->addRow(Row::fromValues($this->map($employee, $contractSlots)));
            });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * The directory query only eager loads the service; the direction is read
     * from the contract here too.
     *
     * @return Builder<Employee>
     */
    private function rowsQuery(): Builder
    {
        return (clone $this->query)->with(['activeContracts.service', 'activeContracts.direction']);
    }
}
