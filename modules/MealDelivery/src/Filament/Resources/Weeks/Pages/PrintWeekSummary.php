<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use AcMarche\MealDelivery\Service\WeekDaysSummaryAggregator;
use App\Models\User;
use Filament\Resources\Pages\Page;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class PrintWeekSummary extends Page
{
    use MealDeliveryAuthorization;

    public Week $record;

    /**
     * @var array<int, array{date: string, label: string, clients_count: int, soup_count: int, menu1_count: int, menu2_count: int}>
     */
    public array $days = [];

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.print-week-summary';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(Week $record): void
    {
        $this->record = $record;
        $this->days = (new WeekDaysSummaryAggregator())->build($record);
    }

    public function getTitle(): string
    {
        return 'Semaine du '.$this->record->formattedFirstDay();
    }

    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]) => $this->getTitle(),
            'Impression',
        ];
    }

    public function downloadPdf(): StreamedResponse
    {
        $filename = 'semaine-'.($this->record->first_day?->format('Y-m-d') ?? $this->record->id).'.pdf';

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.resources.weeks.pages.print-week-summary-pdf', [
                        'heading' => $this->getTitle(),
                        'days' => $this->days,
                        'totals' => $this->getTotals(),
                    ])
                    ->landscape()
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

    /**
     * @return array{clients_count: int, soup_count: int, menu1_count: int, menu2_count: int}
     */
    public function getTotals(): array
    {
        return [
            'clients_count' => (int) array_sum(array_column($this->days, 'clients_count')),
            'soup_count' => (int) array_sum(array_column($this->days, 'soup_count')),
            'menu1_count' => (int) array_sum(array_column($this->days, 'menu1_count')),
            'menu2_count' => (int) array_sum(array_column($this->days, 'menu2_count')),
        ];
    }
}
