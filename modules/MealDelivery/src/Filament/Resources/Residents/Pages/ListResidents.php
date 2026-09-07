<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Pages;

use AcMarche\MealDelivery\Filament\Resources\Residents\ResidentResource;
use AcMarche\MealDelivery\Models\Resident;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Override;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Spatie\LaravelPdf\Support\pdf;

final class ListResidents extends ListRecords
{
    #[Override]
    protected static string $resource = ResidentResource::class;

    public function getTitle(): string
    {
        return 'Liste des résidents';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un résident')
                ->icon('tabler-plus'),

            Action::make('downloadDirectory')
                ->label('Annuaire PDF')
                ->icon(Heroicon::ArrowDownTray)
                ->color('info')
                ->modal()
                ->modalHeading('Annuaire des résidents')
                ->modalSubmitActionLabel('Télécharger')
                ->schema([
                    Toggle::make('include_inactive')
                        ->label('Inclure les résidents inactifs')
                        ->default(false),
                ])
                ->action(fn (array $data): StreamedResponse => $this->downloadDirectory(
                    (bool) ($data['include_inactive'] ?? false),
                )),
        ];
    }

    /**
     * @return Collection<int, Resident>
     */
    private static function residents(bool $includeInactive): Collection
    {
        return Resident::query()
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    private function downloadDirectory(bool $includeInactive): StreamedResponse
    {
        $filename = 'residents-'.CarbonImmutable::now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            function () use ($filename, $includeInactive): void {
                echo pdf()
                    ->view('meal-delivery::filament.resources.residents.pages.residents-pdf', [
                        'residents' => self::residents($includeInactive),
                        'includeInactive' => $includeInactive,
                        'printedAt' => CarbonImmutable::now(),
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
