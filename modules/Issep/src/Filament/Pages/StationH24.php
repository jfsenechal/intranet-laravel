<?php

declare(strict_types=1);

namespace AcMarche\Issep\Filament\Pages;

use AcMarche\Issep\Dto\Indice;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\StationRepository;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Override;

/**
 * The recent BelAQI readings of one station, over the last three days by default.
 *
 * The index is published hourly but a station only reports a handful of them a day, so a
 * 24 hour window often holds very little; three days is enough to read a trend. The period
 * filter goes from 24 hours to six days, which is as far back as /belaqi reaches: it answers
 * with the last thousand readings of the whole network and takes no date range.
 *
 * The station is identified by its `id` (as in the stations table), not by its sensor
 * configuration id, so a link stays valid when the station is re-equipped. The route keeps
 * its `h24` slug, which is what the intranet has always linked to.
 */
final class StationH24 extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * Hours of history the page opens on.
     */
    private const DEFAULT_PERIOD_HOURS = '72';

    /**
     * The windows offered by the period filter, in hours.
     *
     * Six days is the ceiling because that is all the API holds: /belaqi answers with the last
     * thousand readings of the whole network and takes no date range, which works out at about
     * six days. Offering a month would have shown exactly the same rows as a week.
     *
     * @var array<string, string>
     */
    private const PERIODS = [
        '24' => '24 dernières heures',
        '48' => '48 dernières heures',
        '72' => '3 derniers jours',
        '144' => '6 derniers jours',
    ];

    /**
     * How far short of the chosen window the history may fall before the table mentions it.
     *
     * The thousand readings the API keeps span a little under six days and the exact boundary
     * drifts with how much the network reports, so a couple of hours' shortfall on the widest
     * window is normal and not worth a notice. A station that only came online days ago is.
     */
    private const COVERAGE_SLACK_HOURS = 12;

    public int $stationId = 0;

    public ?string $stationName = null;

    public ?int $idConfiguration = null;

    public ?string $apiError = null;

    #[Override]
    protected string $view = 'issep::filament.pages.station-h24';

    #[Override]
    protected static ?string $slug = 'station/{station}/h24';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Everything the page needs from the station is read once here and kept in Livewire
     * properties: a render-time read would re-hit the API on every table interaction, and the
     * 404 guard belongs to the initial load rather than to an update request.
     */
    public function mount(int $station): void
    {
        $this->stationId = $station;

        try {
            $found = app(StationRepository::class)->station($station);
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            return;
        }

        abort_if($found === null, 404, 'Station non trouvée.');

        $this->stationName = $found->nom;
        $this->idConfiguration = $found->idConfiguration;
    }

    public function getTitle(): string
    {
        return $this->stationName === null
            ? 'Relevés de la station'
            : "Les derniers relevés de la station {$this->stationName}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (array $filters): array => $this->loadRecords($filters))
            ->heading('Historique des relevés')
            ->columns([
                TextColumn::make('ts')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Inconnue'),
                TextColumn::make('label')
                    ->label('Indice BelAQI')
                    ->badge()
                    ->color(fn (array $record): string => $record['color']),
                TextColumn::make('aqi_value')
                    ->label('Valeur'),
            ])
            ->description(fn (): ?string => $this->getCoverageNotice())
            ->filters([
                SelectFilter::make('period')
                    ->label('Période')
                    ->options(self::PERIODS)
                    ->default(self::DEFAULT_PERIOD_HOURS)
                    ->selectablePlaceholder(false),
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading($this->apiError ?? 'Aucun relevé sur la période')
            ->emptyStateIcon(Heroicon::OutlinedSignalSlash);
    }

    /**
     * Says where the history begins when the chosen period reaches further back than the API.
     *
     * Read from the filter state rather than set while loading the records: the table header
     * renders before the body pulls them, so a value set there would always be one render late.
     */
    public function getCoverageNotice(): ?string
    {
        if ($this->idConfiguration === null) {
            return null;
        }

        try {
            $oldest = app(StationRepository::class)->oldestBelAqiTimestamp($this->idConfiguration);
        } catch (IssepException) {
            return null;
        }

        return self::coverageNotice($oldest, self::sinceFromFilters($this->tableFilters ?? []));
    }

    /**
     * The last reading of the station, shown above the history.
     *
     * @return array<string, mixed>|null
     */
    public function getLastIndice(): ?array
    {
        if ($this->idConfiguration === null) {
            return null;
        }

        try {
            return app(StationRepository::class)
                ->lastBelAqiForStation($this->idConfiguration)
                ?->toArray();
        } catch (IssepException) {
            return null;
        }
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('config')
                ->label('Configuration')
                ->icon(Heroicon::Cog6Tooth)
                ->color('gray')
                ->visible(fn (): bool => $this->stationName !== null)
                ->url(fn (): string => StationConfig::getUrl(['station' => $this->stationId])),
            Action::make('back')
                ->label('Toutes les stations')
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('gray')
                ->url(fn (): string => Stations::getUrl()),
        ];
    }

    /**
     * @return array{lastIndice: array<string, mixed>|null}
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'lastIndice' => $this->getLastIndice(),
        ];
    }

    /**
     * The start of the window the period filter is asking for.
     *
     * An unrecognised value falls back to the default window rather than to everything: the
     * point of the fixed list is that no request reaches further back than the API holds.
     *
     * @param  array<string, mixed>  $filters
     */
    private static function sinceFromFilters(array $filters): CarbonInterface
    {
        $period = $filters['period']['value'] ?? self::DEFAULT_PERIOD_HOURS;

        $hours = is_numeric($period) && isset(self::PERIODS[(string) $period])
            ? (int) $period
            : (int) self::DEFAULT_PERIOD_HOURS;

        return now()->subHours($hours);
    }

    /**
     * Says where the history begins when the chosen period reaches further back than the API.
     *
     * /belaqi returns the last thousand readings of the whole network and accepts no date
     * range, which is currently about six days. Without this, choosing "3 mois" would silently
     * show the same rows as "1 semaine" with nothing to explain why.
     *
     * The comparison has to be against the oldest reading the API holds at all, not the oldest
     * one in the filtered result: that one always falls inside the window by construction.
     */
    private static function coverageNotice(?CarbonImmutable $oldest, CarbonInterface $since): ?string
    {
        if (! $oldest instanceof CarbonImmutable) {
            return null;
        }

        if ($oldest->lessThanOrEqualTo($since->copy()->addHours(self::COVERAGE_SLACK_HOURS))) {
            return null;
        }

        return "L'API ISSEP ne conserve que les 1000 derniers relevés du réseau: l'historique "
            ."de cette station commence le {$oldest->translatedFormat('j F Y à H:i')}.";
    }

    /**
     * The timestamp identifies a reading of a station; the position is only a fallback for a
     * reading the API sent without one.
     */
    private static function recordKey(Indice $indice, int $position): string
    {
        return $indice->ts?->format('YmdHis') ?? "position-{$position}";
    }

    /**
     * The readings of this station, newest first, keyed by timestamp.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, array<string, mixed>>
     */
    private function loadRecords(array $filters): array
    {
        if ($this->idConfiguration === null) {
            return [];
        }

        $since = self::sinceFromFilters($filters);

        try {
            $indices = app(StationRepository::class)->belAqiForStation($this->idConfiguration, $since);
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            Notification::make()
                ->title("Erreur de lecture de l'API ISSEP")
                ->body($issepException->getMessage())
                ->danger()
                ->send();

            return [];
        }

        $records = [];

        foreach ($indices as $position => $indice) {
            $records[self::recordKey($indice, $position)] = $indice->toArray();
        }

        return $records;
    }
}
