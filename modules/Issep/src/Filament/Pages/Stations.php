<?php

declare(strict_types=1);

namespace AcMarche\Issep\Filament\Pages;

use AcMarche\Issep\Dto\Indice;
use AcMarche\Issep\Dto\Station;
use AcMarche\Issep\Enums\IndiceEnum;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\StationRepository;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Override;

/**
 * The stations of the ISSEP microsensor network with their current air quality index.
 *
 * A Page rather than a Resource: nothing is stored locally, every row is read live from the
 * ISSEP API, so the table is fed by the custom-data records() function instead of a query
 * builder.
 */
final class Stations extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * The API error of the current render, shown in the empty state so the page explains
     * itself instead of just coming up empty.
     */
    public ?string $apiError = null;

    #[Override]
    protected string $view = 'issep::filament.pages.stations';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    #[Override]
    protected static ?string $navigationLabel = 'Stations';

    #[Override]
    protected static ?int $navigationSort = 0;

    #[Override]
    protected static ?string $slug = 'stations';

    public function getTitle(): string
    {
        return "Stations de mesure de la qualité de l'air";
    }

    public function getSubheading(): ?string
    {
        return 'Indice BelAQI relevé par les capteurs ISSEP du réseau de Marche-en-Famenne.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection): array => $this->loadRecords($search, $sortColumn, $sortDirection))
            ->columns([
                TextColumn::make('id')
                    ->label('Numéro')
                    ->sortable(),
                TextColumn::make('nom')
                    ->label('Nom')
                    ->weight(FontWeight::Medium)
                    ->sortable(),
                TextColumn::make('belaqi')
                    ->label('Indice BelAQI')
                    ->badge()
                    ->color(fn (array $record): string => $record['belaqi_color'])
                    ->tooltip(fn (array $record): ?string => $record['is_fixed']
                        ? "Relevé du réseau de secours, le capteur de la station n'a pas de mesure récente"
                        : null)
                    ->sortable(),
                TextColumn::make('belaqi_ts')
                    ->label('Dernier relevé')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Aucun relevé')
                    ->sortable(),
                TextColumn::make('is_fixed')
                    ->label('Corrigé')
                    ->badge()
                    ->color('gray')
                    ->state(fn (array $record): ?string => $record['is_fixed'] ? 'Corrigé' : null)
                    ->placeholder('—'),
            ])
            ->defaultSort('nom')
            ->searchable()
            ->searchPlaceholder('Rechercher une station')
            ->paginated(false)
            ->emptyStateHeading($this->apiError ?? 'Aucune station')
            ->emptyStateDescription($this->apiError !== null
                ? "Les données n'ont pas pu être lues sur l'API ISSEP."
                : null)
            ->emptyStateIcon(Heroicon::OutlinedSignalSlash)
            ->recordActions([
                Action::make('h24')
                    ->label('Relevés 24h')
                    ->icon(Heroicon::ChartBar)
                    ->color('primary')
                    ->url(fn (array $record): string => StationH24::getUrl(['station' => $record['id']])),
                Action::make('config')
                    ->label('Configuration')
                    ->icon(Heroicon::Cog6Tooth)
                    ->color('gray')
                    ->url(fn (array $record): string => StationConfig::getUrl(['station' => $record['id']])),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('legend')
                ->label('Légende')
                ->icon(Heroicon::QuestionMarkCircle)
                ->color('gray')
                ->modalHeading("L'indice BelAQI")
                ->modalWidth(Width::Medium)
                ->modalContent(fn () => view('issep::filament.belaqi-legend', [
                    'indices' => array_filter(
                        IndiceEnum::cases(),
                        fn (IndiceEnum $indice): bool => $indice->hasReading(),
                    ),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::ArrowPath)
                ->color('gray')
                ->action(function (): void {
                    app(StationRepository::class)->refresh();
                    $this->resetTable();

                    Notification::make()
                        ->title('Données rechargées depuis ISSEP')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function toRecord(Station $station, ?Indice $indice): array
    {
        $enum = $indice?->indice() ?? IndiceEnum::NO_DATA;

        return [
            'id' => $station->id,
            'nom' => $station->nom,
            'id_configuration' => $station->idConfiguration,
            'belaqi' => $indice?->labelWithValue() ?? $enum->label(),
            'belaqi_value' => $indice?->aqiValue ?? $enum->value,
            'belaqi_color' => $enum->colorName(),
            'belaqi_ts' => $indice?->ts,
            'is_fixed' => $indice?->isFixed ?? false,
        ];
    }

    /**
     * The whole network is a handful of stations, so the search is applied here rather than
     * asking the API for it (it has no search endpoint).
     *
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private static function filterBySearch(array $records, ?string $search): array
    {
        if (blank($search)) {
            return $records;
        }

        $needle = Str::lower($search);

        return array_filter(
            $records,
            fn (array $record): bool => str_contains(Str::lower((string) $record['nom']), $needle)
                || str_contains((string) $record['id'], $needle)
                || str_contains(Str::lower((string) $record['belaqi']), $needle),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     * @return array<int, array<string, mixed>>
     */
    private static function sort(array $records, ?string $sortColumn, ?string $sortDirection): array
    {
        $key = match ($sortColumn) {
            'id' => 'id',
            'belaqi' => 'belaqi_value',
            'belaqi_ts' => 'belaqi_ts',
            default => 'nom',
        };

        $descending = $sortDirection === 'desc';

        uasort($records, function (array $a, array $b) use ($key, $descending): int {
            $comparison = $key === 'belaqi_ts'
                ? ($a['belaqi_ts']?->getTimestamp() ?? 0) <=> ($b['belaqi_ts']?->getTimestamp() ?? 0)
                : $a[$key] <=> $b[$key];

            return $descending ? -$comparison : $comparison;
        });

        return $records;
    }

    /**
     * One row per station, keyed by station id so Filament tracks the rows across Livewire
     * updates and sends a record action to the station it was clicked on.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadRecords(?string $search, ?string $sortColumn, ?string $sortDirection): array
    {
        $this->apiError = null;

        $repository = app(StationRepository::class);

        try {
            $stations = $repository->stations();

            $records = [];

            foreach ($stations as $station) {
                $indice = $repository->lastBelAqiForStation($station->idConfiguration, withFallback: true);

                $records[$station->id] = self::toRecord($station, $indice);
            }
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            Notification::make()
                ->title("Erreur de lecture de l'API ISSEP")
                ->body($issepException->getMessage())
                ->danger()
                ->send();

            return [];
        }

        $records = self::filterBySearch($records, $search);

        return self::sort($records, $sortColumn, $sortDirection);
    }
}
