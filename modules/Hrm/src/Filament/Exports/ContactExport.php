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
            'first_name' => 'Prénom',
            'email_1' => 'Email 1',
            'email_2' => 'Email 2',
            'phone_1' => 'Téléphone 1',
            'phone_2' => 'Téléphone 2',
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
    public function map(Contact $row): array
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

            $this->query->lazy()->each(function (Contact $contact) use ($writer): void {
                $writer->addRow(Row::fromValues($this->map($contact)));
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
    private function row(Contact $row): array
    {
        return [
            'last_name' => $row->last_name,
            'first_name' => $row->first_name,
            'email_1' => $row->email_1,
            'email_2' => $row->email_2,
            'phone_1' => $row->phone_1,
            'phone_2' => $row->phone_2,
            'created_at' => $row->created_at?->format('d/m/Y H:i'),
        ];
    }
}
