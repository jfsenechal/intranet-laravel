<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Pages;

use AcMarche\MealDelivery\Policies\Concerns\MealDeliveryAuthorization;
use AcMarche\MealDelivery\Service\DailyGuestsAggregator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

use function Spatie\LaravelPdf\Support\pdf;

/**
 * The daily sheet handed to the kitchen for the guest meals of the home. It is
 * deliberately kept apart from the meal delivery exports: guests eat on the spot,
 * are never delivered, and their residents are not clients of the service.
 */
final class GuestsKitchenExport extends Page
{
    use MealDeliveryAuthorization;

    #[Url(as: 'date')]
    public string $date = '';

    protected static ?string $slug = 'guests-kitchen-export';

    protected static ?int $navigationSort = 9;

    protected static string|UnitEnum|null $navigationGroup = 'Invités';

    protected string $view = 'meal-delivery::filament.pages.guests-kitchen-export';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationLabel(): string
    {
        return 'Export cuisine invités';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user instanceof User && self::canAccessStatic($user);
    }

    public function mount(): void
    {
        $this->date = self::normalizeDate($this->date);
    }

    public function getTitle(): string
    {
        return 'Repas invités du '.$this->formattedDate();
    }

    /**
     * @return array{
     *     date: CarbonImmutable,
     *     rows: list<array{resident_name: string, room: ?string, menu1: int, menu2: int, total: int, notes: ?string}>,
     *     totals: array{residents: int, menu1: int, menu2: int, guests: int}
     * }
     */
    public function getSummary(): array
    {
        return (new DailyGuestsAggregator())->build($this->date);
    }

    public function formattedDate(): string
    {
        return Str::title(CarbonImmutable::parse($this->date)->translatedFormat('l j F Y'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('search')
                ->label('Choisir une date')
                ->icon('tabler-search')
                ->color('primary')
                ->modal()
                ->modalHeading('Choisir le jour')
                ->modalSubmitActionLabel('Afficher')
                ->fillForm(fn (): array => ['date' => $this->date])
                ->schema([
                    DatePicker::make('date')
                        ->label('Date')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->date = self::normalizeDate((string) $data['date']);
                }),

            Action::make('downloadPdf')
                ->label('Télécharger PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->action(fn (): StreamedResponse => $this->downloadPdf()),
        ];
    }

    private static function normalizeDate(string $date): string
    {
        if (mb_trim($date) === '') {
            return CarbonImmutable::now()->format('Y-m-d');
        }

        return CarbonImmutable::parse($date)->format('Y-m-d');
    }

    private function downloadPdf(): StreamedResponse
    {
        $filename = 'invites-'.$this->date.'.pdf';

        return response()->streamDownload(
            function () use ($filename): void {
                echo pdf()
                    ->view('meal-delivery::filament.pages.guests-kitchen-export-pdf', [
                        'summary' => $this->getSummary(),
                        'formattedDate' => $this->formattedDate(),
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
