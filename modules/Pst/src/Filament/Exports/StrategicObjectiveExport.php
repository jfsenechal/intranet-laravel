<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Exports;

use AcMarche\Pst\Models\Odd;
use AcMarche\Pst\Models\Partner;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class StrategicObjectiveExport implements FromCollection, ShouldAutoSize, WithStyles
{
    private static ?string $model = StrategicObjective::class;

    private array $styles = [];

    private array $titles = [
        'OS',
        'OO',
        'Actions',
        'Type',
        'Mandataires',
        'Agents',
        'Services porteurs',
        'Services partenaires',
        'Partenaires',
        'Etat avancement',
        'ODDS',
        'Action requise odd',
        'Synergie Ville-Cpas',
    ];

    public function __construct(private readonly string $department) {}

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your strategic objective export has completed and '.number_format($export->successful_rows).' '.str(
            'row'
        )->plural($export->successful_rows).' exported.';

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    public function collection(): Collection
    {
        $data = collect();
        $strategicObjectives = StrategicObjective::query()
            ->forDepartment($this->department)
            ->with([
                'oos',
                'oos.actions',
                'oos.actions.leaderServices',
                'oos.actions.partnerServices',
                'oos.actions.mandataries',
                'oos.actions.users',
                'oos.actions.partners',
                'oos.actions.odds',
            ])
            ->get();

        $data->push($this->titles);
        $ligne = 2;
        $this->styles[] = "C$ligne";
        foreach ($strategicObjectives as $strategic) {
            // Strategic Objective row
            $data->push([
                'O.S '.$strategic->position,
                null,
                $strategic->name,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ]);

            foreach ($strategic->oos as $operational) {
                $ligne++;
                $this->styles[] = "C$ligne";
                // Operational Objective row
                $data->push([
                    null,
                    'O.O '.$strategic->position.'.'.$operational->position,
                    $operational->name,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                ]);
                foreach ($operational->actions as $action) {
                    $ligne++;
                    $type = $action->type?->name ?? null;
                    $mandataires = $action->mandataries ?? new Collection();
                    $mandatairesNames = $mandataires->map(fn (User $user): string => $user->last_name.' '.$user->first_name);
                    $agents = $action->users ?? new Collection();
                    $agentsNames = $agents->map(fn (User $user): string => $user->last_name.' '.$user->first_name);

                    $servicesPorteurs = $action->leaderServices ?? new Collection();
                    $servicesPorteursNames = $servicesPorteurs->map(fn (Service $service) => $service->name);

                    $servicesPartenaires = $action->partnerServices ?? new Collection();
                    $servicesPartenairesNames = $servicesPartenaires->map(fn (Service $service) => $service->name);

                    $partenaires = $action->partners ?? new Collection();
                    $partenairesNames = $partenaires->map(fn (Partner $partner) => $partner->name);

                    $etatavancement = $action->state?->value ?? null;
                    $odds = $action->odds ?? new Collection();
                    $oddsNames = $odds->map(fn (Odd $odd) => $odd->name);
                    $roadmap = $action->roadmap?->value ?? null;
                    $synergie = $action->synergie?->value ?? null;

                    // Action row
                    $data->push([
                        null,
                        'Action '.$action->id,
                        $action->name,
                        $type,
                        implode(',', $mandatairesNames->toArray()),
                        implode(',', $agentsNames->toArray()),
                        implode(',', $servicesPorteursNames->toArray()),
                        implode(',', $servicesPartenairesNames->toArray()),
                        implode(',', $partenairesNames->toArray()),
                        $etatavancement,
                        implode(',', $oddsNames->toArray()),
                        $roadmap,
                        $synergie,
                    ]);
                }
            }
            $ligne++;
            $this->styles[] = "C$ligne";
        }

        return $data;
    }

    public function styles(Worksheet $sheet): array
    {

        foreach ($this->styles as $style) {
            $sheet->getStyle($style)->getFont()->setBold(true);
        }

        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            //   2 => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }
}
