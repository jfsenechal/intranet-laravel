<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Exports;

use AcMarche\MailingList\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class ContactExport
{
    /**
     * @param  Builder<Contact>  $query
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
            'email' => 'Email',
            'phone' => 'Téléphone',
            'mobile' => 'GSM',
            'street' => 'Rue',
            'postal_code' => 'Code postal',
            'city' => 'Localité',
            'description' => 'Description',
            'unsubscribed_at' => 'Désinscrit le',
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

            $this->rowsQuery()->lazy()->each(function (Contact $contact) use ($writer): void {
                $writer->addRow(Row::fromValues($this->map($contact)));
            });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * The primary key is appended to the sort so the chunks `lazy()` walks stay
     * deterministic: the table sorts on a column that is not unique, and ties
     * would otherwise let rows repeat or vanish between two pages.
     *
     * @return Builder<Contact>
     */
    private function rowsQuery(): Builder
    {
        return (clone $this->query)
            ->orderBy(new Contact()->getQualifiedKeyName());
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
            'email' => $row->email,
            'phone' => $row->phone,
            'mobile' => $row->mobile,
            'street' => $row->street,
            'postal_code' => $row->postal_code,
            'city' => $row->city,
            'description' => $row->description,
            'unsubscribed_at' => display_datetime($row->unsubscribed_at),
        ];
    }
}
