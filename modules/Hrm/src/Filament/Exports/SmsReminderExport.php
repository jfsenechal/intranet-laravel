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
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Agent',
            'Numéro',
            'Date de rappel',
            'Autre date de rappel',
            'Envoyé le',
            'Résultat',
            'Créé par',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(SmsReminder $row): array
    {
        return [
            mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            $row->phone_number,
            $row->reminder_date?->format('d/m/Y'),
            $row->other_reminder_date?->format('d/m/Y'),
            $row->sent_at?->format('d/m/Y'),
            $row->result,
            $row->updated_by,
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
                ->each(function (SmsReminder $reminder) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($reminder)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
