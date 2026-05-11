<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\RouteSheetsAggregator;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\Page;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class RouteSheets extends Page
{
    public Week $record;

    public string $date;

    /**
     * @var array{
     *     date: CarbonImmutable,
     *     routes: array<int, array{id: int, name: string, rows: list<array<string, mixed>>, totals: array<string, int>}>,
     *     cafeteria: array{name: string, rows: list<array<string, mixed>>, totals: array<string, int>}
     * }
     */
    public array $sheets;

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.route-sheets';

    public function mount(Week $record, string $date): void
    {
        abort_unless(auth()->user()?->can('meal-delivery-access'), 403);

        $this->record = $record;
        $this->date = CarbonImmutable::parse($date)->format('Y-m-d');
        $this->sheets = (new RouteSheetsAggregator())->build($record, $this->date);
    }

    public function getTitle(): string
    {
        return 'Feuilles de route du '.CarbonImmutable::parse($this->date)->translatedFormat('l j F Y');
    }

    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]) => 'Semaine du '.$this->record->formattedFirstDay(),
            $this->getTitle(),
        ];
    }

    public function downloadRoutePdf(int $routeId): StreamedResponse
    {
        $sheet = collect($this->sheets['routes'])->firstWhere('id', $routeId);

        if (! is_array($sheet)) {
            abort(404);
        }

        $filename = 'feuille-route-'.$routeId.'-'.CarbonImmutable::parse($this->date)->format('Y-m-d').'.pdf';

        return $this->streamPdf(
            'meal-delivery::filament.resources.weeks.pages.route-sheet-pdf',
            [
                'date' => $this->sheets['date'],
                'sheet' => $sheet,
                'heading' => $sheet['name'].' : '.$this->frenchDate(),
            ],
            $filename,
        );
    }

    public function downloadCafeteriaPdf(): StreamedResponse
    {
        $filename = 'cafeteria-'.CarbonImmutable::parse($this->date)->format('Y-m-d').'.pdf';

        return $this->streamPdf(
            'meal-delivery::filament.resources.weeks.pages.route-sheet-pdf',
            [
                'date' => $this->sheets['date'],
                'sheet' => $this->sheets['cafeteria'],
                'heading' => 'Cafétéria : '.$this->frenchDate(),
            ],
            $filename,
        );
    }

    private function frenchDate(): string
    {
        return CarbonImmutable::parse($this->date)->translatedFormat('l j F Y');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function streamPdf(string $view, array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($view, $data, $filename): void {
                echo pdf()
                    ->view($view, $data)
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
