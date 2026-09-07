<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Pages;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use AcMarche\MealDelivery\Service\RouteSheetsAggregator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\Page;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class CafeteriaSheet extends Page
{
    use MealDeliveryAuthorization;

    public Week $record;

    public string $date;

    /**
     * @var array{name: string, rows: list<array<string, mixed>>, totals: array<string, int>}
     */
    public array $sheet;

    #[Override]
    protected static string $resource = WeekResource::class;

    protected string $view = 'meal-delivery::filament.resources.weeks.pages.cafeteria-sheet';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(Week $record, string $date): void
    {
        $this->record = $record;
        $this->date = CarbonImmutable::parse($date)->format('Y-m-d');
        $this->sheet = (new RouteSheetsAggregator())->build($record, $this->date)['cafeteria'];
    }

    public function getTitle(): string
    {
        return 'Cafétariat du '.CarbonImmutable::parse($this->date)->translatedFormat('l j F Y');
    }

    public function getBreadcrumbs(): array
    {
        return [
            WeekResource::getUrl() => 'Semaines',
            WeekResource::getUrl('view', ['record' => $this->record->id]
            ) => 'Semaine du '.$this->record->formattedFirstDay(),
            $this->getTitle(),
        ];
    }

    public function downloadPdf(): StreamedResponse
    {
        $filename = 'cafetariat-'.CarbonImmutable::parse($this->date)->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.resources.weeks.pages.route-sheet-pdf', [
                        'date' => CarbonImmutable::parse($this->date),
                        'sheet' => $this->sheet,
                        'heading' => 'Cafétariat : '.CarbonImmutable::parse($this->date)->translatedFormat('l j F Y'),
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
