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
            'name' => 'Intitulé',
            'agent' => 'Agent',
            'employer' => 'Employeur',
            'start_date' => 'Début',
            'end_date' => 'Fin',
            'reminder_date' => 'Rappel',
            'closed_date' => 'Clôture',
            'is_closed' => 'Clôturée',
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
    public function map(Deadline $row): array
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
    private function row(Deadline $row): array
    {
        return [
            'name' => $row->name,
            'agent' => mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            'employer' => $row->employer?->name,
            'start_date' => $row->start_date?->format('d/m/Y'),
            'end_date' => $row->end_date?->format('d/m/Y'),
            'reminder_date' => $row->reminder_date?->format('d/m/Y'),
            'closed_date' => $row->closed_date?->format('d/m/Y'),
            'is_closed' => $row->is_closed ? 'Oui' : 'Non',
        ];
    }
}
