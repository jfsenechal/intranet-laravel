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
            'user_add' => 'Agent',
            'full_name' => 'Nom complet',
            'location_type' => 'Lieu',
            'day_type' => 'Type de jour',
            'fixed_day' => 'Jour fixe',
            'manager_validated' => 'Validé direction',
            'manager_validated_at' => 'Validé le',
            'hr_validator_name' => 'Validation Grh par',
            'created_at' => 'Créé le',
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
    public function map(Telework $row): array
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
    private function row(Telework $row): array
    {
        $fullName = $row->employee
            ? mb_trim(($row->employee->last_name ?? '').' '.($row->employee->first_name ?? ''))
            : '';

        return [
            'user_add' => $row->user_add,
            'full_name' => $fullName,
            'location_type' => $row->location_type?->getLabel(),
            'day_type' => $row->day_type?->getLabel(),
            'fixed_day' => $row->fixed_day?->getLabel(),
            'manager_validated' => $row->manager_validated ? 'Oui' : 'Non',
            'manager_validated_at' => $row->manager_validated_at?->format('d/m/Y'),
            'hr_validator_name' => $row->hr_validator_name,
            'created_at' => $row->created_at?->format('d/m/Y'),
        ];
    }
}
