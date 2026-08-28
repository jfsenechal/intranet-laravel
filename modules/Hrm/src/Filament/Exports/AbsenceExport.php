<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Absence;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class AbsenceExport
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
            'agent' => 'Agent',
            'start_date' => 'Début',
            'end_date' => 'Fin',
            'days' => 'Nombre de jours',
            'reason' => 'Raison',
            'is_closed' => 'Clôturée',
            'reminder_date' => 'Rappel',
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
     * @return list<int|null|string>
     */
    public function map(Absence $row): array
    {
        $data = $this->row($row);

        return array_map(fn (string $key): int|string|null => $data[$key], $this->selectedColumns());
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
                ->each(function (Absence $absence) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($absence)));
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
     * Inclusive number of calendar days covered by the absence, null while it is still open.
     */
    private function durationInDays(Absence $row): ?int
    {
        if (! $row->start_date || ! $row->end_date) {
            return null;
        }

        return (int) $row->start_date->diffInDays($row->end_date, true) + 1;
    }

    /**
     * @return array<string, int|null|string>
     */
    private function row(Absence $row): array
    {
        return [
            'agent' => mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            'start_date' => $row->start_date?->format('d/m/Y'),
            'end_date' => $row->end_date?->format('d/m/Y'),
            'days' => $this->durationInDays($row),
            'reason' => $row->reason?->getLabel(),
            'is_closed' => $row->is_closed ? 'Oui' : 'Non',
            'reminder_date' => $row->reminder_date?->format('d/m/Y'),
        ];
    }
}
