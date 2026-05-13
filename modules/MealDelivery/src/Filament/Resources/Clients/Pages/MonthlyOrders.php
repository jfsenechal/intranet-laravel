<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use AcMarche\MealDelivery\Service\ClientMonthlyOrdersAggregator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class MonthlyOrders extends Page
{
    use MealDeliveryAuthorization;

    public Client $record;

    public int $month;

    public int $year;

    /**
     * @var array{
     *     period: CarbonImmutable,
     *     rows: list<array{date: CarbonImmutable, soup_count: int, menu_1: int, menu_2: int}>,
     *     totals: array{soup: int, menu_1: int, menu_2: int, menus: int}
     * }
     */
    public array $summary;

    #[Override]
    protected static string $resource = ClientResource::class;

    protected string $view = 'meal-delivery::filament.resources.clients.pages.monthly-orders';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(Client $record, string $month, string $year): void
    {
        $this->record = $record;
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->summary = (new ClientMonthlyOrdersAggregator())->build($record, $this->month, $this->year);
    }

    public function getTitle(): string
    {
        return $this->record->last_name.' '.$this->record->first_name
            .' — commandes en '.$this->formattedPeriod();
    }

    public function getBreadcrumbs(): array
    {
        return [
            ClientResource::getUrl() => 'Clients',
            ClientResource::getUrl('view', ['record' => $this->record->id]) => (string) $this->record,
            $this->getTitle(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Télécharger PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->action(fn (): StreamedResponse => $this->downloadPdf()),
        ];
    }

    private function formattedPeriod(): string
    {
        return Str::title($this->summary['period']->translatedFormat('F Y'));
    }

    private function downloadPdf(): StreamedResponse
    {
        $filename = sprintf(
            'commandes-%s-%s-%02d-%d.pdf',
            Str::slug($this->record->last_name ?? ''),
            Str::slug($this->record->first_name ?? ''),
            $this->month,
            $this->year,
        );

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.resources.clients.pages.monthly-orders-pdf', [
                        'client' => $this->record,
                        'summary' => $this->summary,
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
