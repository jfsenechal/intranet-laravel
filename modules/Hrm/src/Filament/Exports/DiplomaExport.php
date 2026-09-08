<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Diploma;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DiplomaExport
{
    /**
     * @param  Builder<Diploma>  $query
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
            'name' => 'Intitulé',
            'certificate_file' => 'Fichier',
            'user_add' => 'Ajouté par',
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
    public function map(Diploma $row): array
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

            $this->rowsQuery()
                ->lazy()
                ->each(function (Diploma $diploma) use ($writer): void {
                    $writer->addRow(Row::fromValues($this->map($diploma)));
                });

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * The relations the rows read are eager loaded here. The primary key is
     * appended to the sort so the chunks `lazy()` walks stay deterministic:
     * the table sorts on a column that is not unique, and ties would otherwise
     * let rows repeat or vanish between two pages.
     *
     * @return Builder<Diploma>
     */
    private function rowsQuery(): Builder
    {
        return (clone $this->query)
            ->with(['employee'])
            ->orderBy(new Diploma()->getQualifiedKeyName());
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
    private function row(Diploma $row): array
    {
        return [
            'agent' => mb_trim(($row->employee?->last_name ?? '').' '.($row->employee?->first_name ?? '')),
            'name' => $row->name,
            'certificate_file' => $row->certificate_file ? 'Oui' : 'Non',
            'user_add' => $row->user_add,
            'created_at' => $row->created_at?->format('d/m/Y'),
        ];
    }
}
