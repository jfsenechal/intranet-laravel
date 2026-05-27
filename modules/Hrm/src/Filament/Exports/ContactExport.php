<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class ContactExport
{
    public function __construct(private Builder $query) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Email 1',
            'Email 2',
            'Téléphone 1',
            'Téléphone 2',
            'Créé le',
        ];
    }

    /**
     * @return list<null|string>
     */
    public function map(Contact $row): array
    {
        return [
            $row->last_name,
            $row->first_name,
            $row->email_1,
            $row->email_2,
            $row->phone_1,
            $row->phone_2,
            $row->created_at?->format('d/m/Y H:i'),
        ];
    }

    public function downloadXlsx(string $filename): StreamedResponse
    {
        return new StreamedResponse(function (): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $bold = (new Style())->setFontBold();
            $writer->addRow(Row::fromValues($this->headings(), $bold));

            $this->query->lazy()->each(function (Contact $contact) use ($writer): void {
                $writer->addRow(Row::fromValues($this->map($contact)));
            });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
