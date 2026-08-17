<?php

declare(strict_types=1);

namespace AcMarche\Issep\Filament\Pages;

use AcMarche\Issep\Dto\Station;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\StationRepository;
use AcMarche\Issep\Support\MeasurementLabels;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

/**
 * The raw measurements of one station over a date range.
 *
 * The sensors report about once a minute, so a single day is around 1400 rows of 40 fields.
 * The filters are therefore deferred: nothing is asked of the API until the station and the
 * range are both chosen and applied, and only the page being looked at is rendered.
 */
final class StationSearch extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * The columns of the table, on normalised field names.
     *
     * Particles, weather and the concentrations are shown; the raw sensor signals, the status
     * codes and the battery telemetry are available from the column manager. The unit is in
     * the label because the numbers are meaningless without it.
     *
     * @var array<string, array{0: string, 1: bool}>
     */
    private const MEASUREMENTS = [
        'pm1' => ['PM1 (µg/m³)', true],
        'pm25' => ['PM2.5 (µg/m³)', true],
        'pm4' => ['PM4 (µg/m³)', false],
        'pm10' => ['PM10 (µg/m³)', true],
        'bmet' => ['Température (°C)', true],
        'bmerh' => ['Humidité (%)', true],
        'bmepres' => ['Pression (Pa)', false],
        'ugpcmno' => ['NO (µg/m³)', true],
        'ugpcmno2' => ['NO2 (µg/m³)', true],
        'ugpcmo3' => ['O3 (µg/m³)', true],
        'ppbno' => ['NO (ppb)', false],
        'ppbno2' => ['NO2 (ppb)', false],
        'ppbo3' => ['O3 (ppb)', false],
        'co' => ['CO (signal brut)', false],
        'no' => ['NO (signal brut)', false],
        'no2' => ['NO2 (signal brut)', false],
        'o3no2' => ['O3 (signal brut)', false],
        'vbat' => ['Batterie (V)', false],
        'mwhbat' => ['Batterie (mWh)', false],
        'mwhpv' => ['Photovoltaïque (mWh)', false],
        'ppbnostatut' => ['Statut NO (ppb)', false],
        'ppbno2statut' => ['Statut NO2 (ppb)', false],
        'ppbo3statut' => ['Statut O3 (ppb)', false],
        'bmetstatut' => ['Statut température', false],
        'bmerhstatut' => ['Statut humidité', false],
        'bmepresstatut' => ['Statut pression', false],
        'pm1statut' => ['Statut PM1', false],
        'pm25statut' => ['Statut PM2.5', false],
        'pm4statut' => ['Statut PM4', false],
        'pm10statut' => ['Statut PM10', false],
        'vbatstatut' => ['Statut batterie', false],
    ];

    public ?string $apiError = null;

    #[Override]
    protected string $view = 'issep::filament.pages.station-search';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    #[Override]
    protected static ?string $navigationLabel = 'Données brutes';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $slug = 'recherche';

    public function getTitle(): string
    {
        return 'Recherche de données brutes';
    }

    public function getSubheading(): ?string
    {
        return 'Mesures telles que le capteur les a transmises, sur la période choisie.';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (array $filters, int $page, int $recordsPerPage): LengthAwarePaginator => $this->loadRecords($filters, $page, $recordsPerPage))
            ->filters([
                SelectFilter::make('station')
                    ->label('Station')
                    ->options(fn (): array => $this->stationOptions())
                    ->default($this->defaultStationId())
                    ->selectablePlaceholder(false),
                Filter::make('period')
                    ->label('Période')
                    ->schema([
                        DatePicker::make('dateBegin')
                            ->label('Du')
                            ->default(now()->subDay())
                            ->maxDate(now())
                            ->native(false)
                            ->required(),
                        DatePicker::make('dateEnd')
                            ->label('Au')
                            ->default(now())
                            ->maxDate(now())
                            ->native(false)
                            ->required(),
                    ]),
            ])
            ->deferFilters()
            ->filtersApplyAction(fn (Action $action): Action => $action
                ->label('Rechercher')
                ->icon(Heroicon::MagnifyingGlass))
            ->columns([
                TextColumn::make('moment')
                    ->label('Horodatage')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (array $record): ?string => $record['moment']?->diffForHumans()),
                ...self::measurementColumns(),
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(50)
            ->emptyStateHeading($this->apiError ?? 'Aucune mesure sur la période')
            ->emptyStateDescription($this->apiError === null
                ? 'Choisissez une station et une période, puis lancez la recherche.'
                : null)
            ->emptyStateIcon(Heroicon::OutlinedTableCells);
    }

    /**
     * @return array<int, TextColumn>
     */
    private static function measurementColumns(): array
    {
        $columns = [];

        foreach (self::MEASUREMENTS as $key => [$label, $isVisible]) {
            $columns[] = TextColumn::make($key)
                ->label($label)
                ->tooltip(MeasurementLabels::describe($key))
                ->numeric(decimalPlaces: 2)
                ->placeholder('—')
                ->alignEnd()
                ->toggleable(isToggledHiddenByDefault: ! $isVisible);
        }

        return $columns;
    }

    /**
     * A measurement is identified by its timestamp; the position covers a row the API sent
     * without one, and keeps the keys unique either way.
     *
     * @param  array<string, mixed>  $row
     */
    private static function recordKey(array $row, int $index): string
    {
        return $row['moment'] instanceof CarbonImmutable
            ? $row['moment']->format('YmdHis')
            : "position-{$index}";
    }

    private static function parseMoment(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        return CarbonImmutable::parse($value, config('issep.timezone'));
    }

    /**
     * The filter holds whatever the date picker put in the Livewire state, which is a string.
     */
    private static function parseFilterDate(mixed $value): ?CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value->startOfDay();
        }

        if (! is_string($value) || blank($value)) {
            return null;
        }

        return CarbonImmutable::parse($value)->startOfDay();
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private static function emptyPage(int $recordsPerPage, int $page): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], total: 0, perPage: $recordsPerPage, currentPage: $page);
    }

    /**
     * @return array<int, string>
     */
    private function stationOptions(): array
    {
        try {
            $stations = app(StationRepository::class)->stations();
        } catch (IssepException) {
            return [];
        }

        return array_map(fn (Station $station): string => $station->nom, $stations);
    }

    private function defaultStationId(): ?int
    {
        return array_key_first($this->stationOptions());
    }

    /**
     * The measurements of the chosen station over the chosen range, newest first.
     *
     * The API has no pagination, so the range is fetched whole (the client caches it) and the
     * page is cut out of it here.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function loadRecords(array $filters, int $page, int $recordsPerPage): LengthAwarePaginator
    {
        $this->apiError = null;

        $stationId = $filters['station']['value'] ?? null;
        $station = $this->resolveStation(is_numeric($stationId) ? (int) $stationId : null);

        if (! $station instanceof Station) {
            return self::emptyPage($recordsPerPage, $page);
        }

        $dateBegin = self::parseFilterDate($filters['period']['dateBegin'] ?? null) ?? CarbonImmutable::yesterday();
        $dateEnd = self::parseFilterDate($filters['period']['dateEnd'] ?? null) ?? CarbonImmutable::today();

        if ($dateEnd->lessThan($dateBegin)) {
            Notification::make()
                ->title('La date de fin précède la date de début')
                ->warning()
                ->send();

            return self::emptyPage($recordsPerPage, $page);
        }

        $dateBegin = $this->clampRange($dateBegin, $dateEnd);

        try {
            $rows = app(StationRepository::class)->measurements($station->idConfiguration, $dateBegin, $dateEnd);
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            Notification::make()
                ->title("Erreur de lecture de l'API ISSEP")
                ->body($issepException->getMessage())
                ->danger()
                ->send();

            return self::emptyPage($recordsPerPage, $page);
        }

        $records = collect($rows)
            ->map(fn (array $row): array => MeasurementLabels::normalizeRow($row))
            ->map(function (array $row): array {
                $row['moment'] = self::parseMoment($row['moment'] ?? null);

                return $row;
            })
            ->sortByDesc(fn (array $row): int => $row['moment']?->getTimestamp() ?? 0)
            ->values()
            ->mapWithKeys(fn (array $row, int $index): array => [
                self::recordKey($row, $index) => $row,
            ]);

        return new LengthAwarePaginator(
            $records->forPage($page, $recordsPerPage),
            total: $records->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * A day of measurements is about 1800 rows, so a range asked over months would download
     * and cache tens of megabytes. The most recent days are the ones being looked for, so the
     * start moves up rather than the end moving back.
     */
    private function clampRange(CarbonImmutable $dateBegin, CarbonImmutable $dateEnd): CarbonImmutable
    {
        $maxDays = max(1, (int) config('issep.max_range_days'));

        if ($dateBegin->diffInDays($dateEnd) < $maxDays) {
            return $dateBegin;
        }

        Notification::make()
            ->title("La période a été ramenée à {$maxDays} jours")
            ->body('Les mesures brutes sont trop volumineuses pour une période plus large.')
            ->warning()
            ->send();

        return $dateEnd->subDays($maxDays - 1)->startOfDay();
    }

    private function resolveStation(?int $stationId): ?Station
    {
        try {
            $repository = app(StationRepository::class);

            return $stationId === null
                ? null
                : $repository->station($stationId);
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            return null;
        }
    }
}
