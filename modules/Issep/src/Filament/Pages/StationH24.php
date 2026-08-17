<?php

declare(strict_types=1);

namespace AcMarche\Issep\Filament\Pages;

use AcMarche\Issep\Dto\Indice;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\StationRepository;
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
 * The BelAQI readings of one station over the last 24 hours.
 *
 * The station is identified by its `id` (as in the stations table), not by its sensor
 * configuration id, so a link stays valid when the station is re-equipped.
 */
final class StationH24 extends Page implements HasTable
{
    use InteractsWithTable;

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
            ->filters([
                SelectFilter::make('period')
                    ->label('Période')
                    ->options([
                        '24' => '24 dernières heures',
                        '48' => '48 dernières heures',
                        'all' => "Tout l'historique",
                    ])
                    ->default('24')
                    ->selectablePlaceholder(false),
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading($this->apiError ?? 'Aucun relevé sur la période')
            ->emptyStateIcon(Heroicon::OutlinedSignalSlash);
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

        $since = match ($filters['period']['value'] ?? '24') {
            '48' => now()->subHours(48),
            'all' => null,
            default => now()->subHours(24),
        };

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
