<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Actions;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Models\Client;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

final class ExportMonthlyOrdersAction
{
    public static function make(): Action
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

        return Action::make('exportMonthlyOrders')
            ->label('Exporter les commandes du mois')
            ->icon('tabler-file-export')
            ->color('info')
            ->modal()
            ->modalHeading('Exporter les commandes')
            ->modalSubmitActionLabel('Voir les commandes')
            ->schema([
                Select::make('month')
                    ->label('Mois')
                    ->options($monthOptions)
                    ->default((int) CarbonImmutable::now()->format('n'))
                    ->required(),
                Select::make('year')
                    ->label('Année')
                    ->options($yearOptions)
                    ->default($currentYear)
                    ->required(),
            ])
            ->action(fn (array $data, Client $record): RedirectResponse => redirect()->to(
                ClientResource::getUrl('monthly-orders', [
                    'record' => $record->id,
                    'month' => (int) $data['month'],
                    'year' => (int) $data['year'],
                ]),
            ));
    }
}
