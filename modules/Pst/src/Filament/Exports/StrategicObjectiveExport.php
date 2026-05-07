<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Exports;

use AcMarche\Pst\Models\Odd;
use AcMarche\Pst\Models\Partner;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use App\Models\User;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StrategicObjectiveExport
{
    /**
     * @var list<string>
     */
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

    public function downloadXlsx(string $filename): StreamedResponse
    {
        return new StreamedResponse(function (): void {
            $writer = new Writer();
            $writer->openToFile('php://output');

            $boldRow = (new Style())->setFontBold();
            $boldName = (new Style())->setFontBold();

            $writer->addRow(Row::fromValues($this->titles, $boldRow));

            foreach ($this->loadStrategicObjectives() as $strategic) {
                $writer->addRow(Row::fromValuesWithStyles(
                    [
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
                    ],
                    null,
                    [2 => $boldName],
                ));

                foreach ($strategic->oos as $operational) {
                    $writer->addRow(Row::fromValuesWithStyles(
                        [
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
                        ],
                        null,
                        [2 => $boldName],
                    ));

                    foreach ($operational->actions as $action) {
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

                        $odds = $action->odds ?? new Collection();
                        $oddsNames = $odds->map(fn (Odd $odd) => $odd->name);

                        $writer->addRow(Row::fromValues([
                            null,
                            'Action '.$action->id,
                            $action->name,
                            $action->type?->name,
                            implode(',', $mandatairesNames->toArray()),
                            implode(',', $agentsNames->toArray()),
                            implode(',', $servicesPorteursNames->toArray()),
                            implode(',', $servicesPartenairesNames->toArray()),
                            implode(',', $partenairesNames->toArray()),
                            $action->state?->value,
                            implode(',', $oddsNames->toArray()),
                            $action->roadmap?->value,
                            $action->synergie?->value,
                        ]));
                    }
                }
            }

            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return Collection<int, StrategicObjective>
     */
    private function loadStrategicObjectives(): Collection
    {
        return StrategicObjective::query()
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
    }
}
