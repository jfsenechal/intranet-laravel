<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Pages;

use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use AcMarche\MealDelivery\Service\AllClientsMonthlyOrdersAggregator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class OrdersByMonth extends Page
{
    use MealDeliveryAuthorization;

    #[Url(as: 'month')]
    public int $month = 0;

    #[Url(as: 'year')]
    public int $year = 0;

    protected static ?string $slug = 'orders-by-month';

    protected static ?int $navigationSort = 3;

    protected string $view = 'meal-delivery::filament.pages.orders-by-month';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-currency-euro';
    }

    public static function getNavigationLabel(): string
    {
        return 'Commandes par mois';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(): void
    {
        if ($this->month < 1 || $this->month > 12) {
            $this->month = (int) CarbonImmutable::now()->format('n');
        }

        if ($this->year < 2000) {
            $this->year = (int) CarbonImmutable::now()->format('Y');
        }
    }

    public function getTitle(): string
    {
        return 'Commandes par mois — '.$this->formattedPeriod();
    }

    /**
     * @return array{
     *     period: CarbonImmutable,
     *     rows: list<array{client: \AcMarche\MealDelivery\Models\Client, soup_total: int, menus_total: int}>,
     *     totals: array{soup: int, menus: int}
     * }
     */
    public function getSummary(): array
    {
        return (new AllClientsMonthlyOrdersAggregator())->build($this->month, $this->year);
    }

    protected function getHeaderActions(): array
    {
        $currentYear = (int) CarbonImmutable::now()->format('Y');

        $monthOptions = collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [
                $month => Str::title(CarbonImmutable::create(null, $month, 1)->translatedFormat('F')),
            ])
            ->all();

        $yearOptions = collect(range($currentYear - 5, $currentYear + 1))
            ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
            ->all();

        return [
            Action::make('search')
                ->label('Rechercher un mois')
                ->icon('tabler-search')
                ->color('primary')
                ->modal()
                ->modalHeading('Choisir le mois et l\'année')
                ->modalSubmitActionLabel('Afficher')
                ->fillForm(fn (): array => ['month' => $this->month, 'year' => $this->year])
                ->schema([
                    Select::make('month')
                        ->label('Mois')
                        ->options($monthOptions)
                        ->required(),
                    Select::make('year')
                        ->label('Année')
                        ->options($yearOptions)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->month = (int) $data['month'];
                    $this->year = (int) $data['year'];
                }),
            Action::make('downloadPdf')
                ->label('Télécharger PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->action(fn (): StreamedResponse => $this->downloadPdf()),
        ];
    }

    private function formattedPeriod(): string
    {
        return Str::title(CarbonImmutable::create($this->year, $this->month, 1)->translatedFormat('F Y'));
    }

    private function downloadPdf(): StreamedResponse
    {
        $filename = sprintf('commandes-%02d-%d.pdf', $this->month, $this->year);

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.pages.orders-by-month-pdf', [
                        'summary' => $this->getSummary(),
                        'period' => $this->formattedPeriod(),
                    ])
                    ->withBrowsershot(function (Browsershot $browsershot): void {
                        if ($path = config('pdf.node_modules_path')) {
                            $browsershot->setNodeModulePath($path);
                        }
                        if ($path = config('pdf.chrome_path')) {
                            $browsershot->setChromePath($path);
                        }
                    })
                    ->name($filename)
                    ->toResponse(request())
                    ->getContent();
            },
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }
}
