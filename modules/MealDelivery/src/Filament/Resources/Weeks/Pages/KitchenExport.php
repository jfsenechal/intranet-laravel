<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\KitchenExportAggregator;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class KitchenExport extends Page
{
    public Week $record;

    public string $date;

    /**
     * @var array{
     *     date: CarbonImmutable,
     *     soup_total: int,
     *     menus_total: int,
     *     menus: array<int, array{position: int, total: int, diets: array<int, array{label: string, total: int}>}>
     * }
     */
    public array $summary;

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.kitchen-export';

    public function mount(Week $record, string $date): void
    {
        $this->record = $record;
        $this->date = CarbonImmutable::parse($date)->format('Y-m-d');
        $this->summary = (new KitchenExportAggregator())->build($record, $this->date);
    }

    public function getTitle(): string
    {
        return 'Export cuisine du '.CarbonImmutable::parse($this->date)->translatedFormat('l j F Y');
    }

    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]) => 'Semaine du '.$this->record->formattedFirstDay(),
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

    private function downloadPdf(): StreamedResponse
    {
        $filename = 'cuisine-'.CarbonImmutable::parse($this->date)->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.resources.weeks.pages.kitchen-export-pdf', [
                        'summary' => $this->summary,
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
