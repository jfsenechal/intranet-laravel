<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\SmsReminder;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class SmsReminderExport
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
            'phone_number' => 'Numéro',
            'reminder_date' => 'Date de rappel',
            'other_reminder_date' => 'Autre date de rappel',
            'sent_at' => 'Envoyé le',
            'result' => 'Résultat',
            'updated_by' => 'Créé par',
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
    public function map(SmsReminder $row): array
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
                ->each(function (SmsReminder $reminder) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($reminder)));
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
    private function row(SmsReminder $row): array
    {
        return [
            'agent' => mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            'phone_number' => $row->phone_number,
            'reminder_date' => $row->reminder_date?->format('d/m/Y'),
            'other_reminder_date' => $row->other_reminder_date?->format('d/m/Y'),
            'sent_at' => $row->sent_at?->format('d/m/Y'),
            'result' => $row->result,
            'updated_by' => $row->updated_by,
        ];
    }
}
