<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Schemas;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\WeekDaysSummaryAggregator;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

final class WeekInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Jours de repas')
                    ->schema([
                        RepeatableEntry::make('days_summary')
                            ->hiddenLabel()
                            ->state(fn (Week $record): array => self::buildDaysSummary($record))
                            ->table([
                                TableColumn::make('Date'),
                                TableColumn::make('Clients')->alignment(Alignment::End),
                                TableColumn::make('Potages')->alignment(Alignment::End),
                                TableColumn::make('Menus 1')->alignment(Alignment::End),
                                TableColumn::make('Menus 2')->alignment(Alignment::End),
                                TableColumn::make('Export cuisine')->alignment(Alignment::End),
                                TableColumn::make('Feuilles de route')->alignment(Alignment::End),
                                TableColumn::make('Cafétariat')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('date')
                                    ->url(function (TextEntry $component): ?string {
                                        $row = $component->getContainer()->getConstantState();

                                        return is_array($row) ? ($row['date_url'] ?? null) : null;
                                    }),
                                TextEntry::make('clients_count')->alignment(Alignment::End),
                                TextEntry::make('soup_count')->alignment(Alignment::End),
                                TextEntry::make('menu1_count')->alignment(Alignment::End),
                                TextEntry::make('menu2_count')->alignment(Alignment::End),
                                TextEntry::make('kitchen_link')
                                    ->alignment(Alignment::End)
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-printer')
                                    ->openUrlInNewTab()
                                    ->url(function (TextEntry $component): ?string {
                                        $row = $component->getContainer()->getConstantState();

                                        return is_array($row) ? ($row['kitchen_url'] ?? null) : null;
                                    }),
                                TextEntry::make('routes_link')
                                    ->alignment(Alignment::End)
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-truck')
                                    ->openUrlInNewTab()
                                    ->url(function (TextEntry $component): ?string {
                                        $row = $component->getContainer()->getConstantState();

                                        return is_array($row) ? ($row['routes_url'] ?? null) : null;
                                    }),
                                TextEntry::make('cafeteria_link')
                                    ->alignment(Alignment::End)
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-building-storefront')
                                    ->openUrlInNewTab()
                                    ->url(function (TextEntry $component): ?string {
                                        $row = $component->getContainer()->getConstantState();

                                        return is_array($row) ? ($row['cafeteria_url'] ?? null) : null;
                                    }),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array{date: string, date_url: string, clients_count: int, soup_count: int, menu1_count: int, menu2_count: int, kitchen_link: string, kitchen_url: string, routes_link: string, routes_url: string, cafeteria_link: string, cafeteria_url: string}>
     */
    private static function buildDaysSummary(Week $week): array
    {
        return array_map(
            static fn (array $day): array => [
                'date' => $day['label'],
                'date_url' => WeekResource::getUrl('day', [
                    'record' => $week->id,
                    'date' => $day['date'],
                ], panel: 'meal-delivery-panel'),
                'clients_count' => $day['clients_count'],
                'soup_count' => $day['soup_count'],
                'menu1_count' => $day['menu1_count'],
                'menu2_count' => $day['menu2_count'],
                'kitchen_link' => 'Exporter',
                'kitchen_url' => WeekResource::getUrl('kitchen', [
                    'record' => $week->id,
                    'date' => $day['date'],
                ], panel: 'meal-delivery-panel'),
                'routes_link' => 'Feuilles',
                'routes_url' => WeekResource::getUrl('routes', [
                    'record' => $week->id,
                    'date' => $day['date'],
                ], panel: 'meal-delivery-panel'),
                'cafeteria_link' => 'Cafétariat',
                'cafeteria_url' => WeekResource::getUrl('cafeteria', [
                    'record' => $week->id,
                    'date' => $day['date'],
                ], panel: 'meal-delivery-panel'),
            ],
            (new WeekDaysSummaryAggregator())->build($week),
        );
    }
}
