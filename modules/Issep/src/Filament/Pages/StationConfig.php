<?php

declare(strict_types=1);

namespace AcMarche\Issep\Filament\Pages;

use AcMarche\Issep\Dto\Station;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\StationRepository;
use AcMarche\Issep\Support\MeasurementLabels;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Override;

/**
 * The sensor configuration of one station: its identity, and the last raw measurement the
 * sensor reported, field by field.
 *
 * The fields are whatever /lastdata returns for the station's configuration rather than a
 * fixed list, so a sensor that reports a new field shows it instead of hiding it.
 */
final class StationConfig extends Page implements HasTable
{
    use InteractsWithTable;

    public int $stationId = 0;

    public ?string $stationName = null;

    public ?int $idConfiguration = null;

    public ?string $apiError = null;

    #[Override]
    protected string $view = 'issep::filament.pages.station-config';

    #[Override]
    protected static ?string $slug = 'station/{station}/config';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

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
            ? 'Configuration de la station'
            : "Configuration de {$this->stationName}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $search): array => $this->loadRecords($search))
            ->heading('Dernière mesure du capteur')
            ->columns([
                TextColumn::make('field')
                    ->label('Champ')
                    ->weight(FontWeight::Medium),
                TextColumn::make('value')
                    ->label('Valeur')
                    ->placeholder('—')
                    ->copyable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->color('gray')
                    ->wrap()
                    ->placeholder('—'),
            ])
            ->searchable()
            ->searchPlaceholder('Rechercher un champ')
            ->paginated(false)
            ->emptyStateHeading($this->apiError ?? 'Aucune mesure pour cette configuration')
            ->emptyStateIcon(Heroicon::OutlinedSignalSlash);
    }

    /**
     * The station itself, for the identity section of the page.
     */
    public function getStation(): ?Station
    {
        try {
            return app(StationRepository::class)->station($this->stationId);
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
            Action::make('h24')
                ->label('Derniers relevés')
                ->icon(Heroicon::ChartBar)
                ->visible(fn (): bool => $this->stationName !== null)
                ->url(fn (): string => StationH24::getUrl(['station' => $this->stationId])),
            Action::make('back')
                ->label('Toutes les stations')
                ->icon(Heroicon::ArrowUturnLeft)
                ->color('gray')
                ->url(fn (): string => Stations::getUrl()),
        ];
    }

    /**
     * @return array{station: Station|null}
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'station' => $this->getStation(),
        ];
    }

    private static function stringify(mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            is_bool($value) => $value ? 'Oui' : 'Non',
            is_array($value) => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }

    /**
     * @param  array<string, array<string, mixed>>  $records
     * @return array<string, array<string, mixed>>
     */
    private static function filterBySearch(array $records, ?string $search): array
    {
        if (blank($search)) {
            return $records;
        }

        $needle = Str::lower($search);

        return array_filter(
            $records,
            fn (array $record): bool => str_contains(Str::lower((string) $record['field']), $needle)
                || str_contains(Str::lower((string) $record['description']), $needle),
        );
    }

    /**
     * One row per field of the measurement, keyed by field name.
     *
     * @return array<string, array<string, mixed>>
     */
    private function loadRecords(?string $search): array
    {
        if ($this->idConfiguration === null) {
            return [];
        }

        try {
            $config = app(StationRepository::class)->config($this->idConfiguration);
        } catch (IssepException $issepException) {
            $this->apiError = $issepException->getMessage();

            Notification::make()
                ->title("Erreur de lecture de l'API ISSEP")
                ->body($issepException->getMessage())
                ->danger()
                ->send();

            return [];
        }

        if ($config === null) {
            return [];
        }

        $records = [];

        foreach ($config as $field => $value) {
            $field = (string) $field;

            $records[$field] = [
                'field' => $field,
                'value' => self::stringify($value),
                'description' => MeasurementLabels::describe($field),
            ];
        }

        return self::filterBySearch($records, $search);
    }
}
