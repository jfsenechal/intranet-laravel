<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Orders\Schemas;

use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Str;

final class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Les repas')
                    ->schema([
                        RepeatableEntry::make('meals_summary')
                            ->hiddenLabel()
                            ->state(fn (Order $record): array => self::buildMealsSummary($record))
                            ->table([
                                TableColumn::make('Jour'),
                                TableColumn::make('Potage')->alignment(Alignment::End),
                                TableColumn::make('Menu 1')->alignment(Alignment::End),
                                TableColumn::make('Menu 2')->alignment(Alignment::End),
                                TableColumn::make('Cafétéria'),
                                TableColumn::make('Remarque'),
                            ])
                            ->schema([
                                TextEntry::make('date'),
                                TextEntry::make('soup_count')->alignment(Alignment::End),
                                TextEntry::make('menu1_count')->alignment(Alignment::End),
                                TextEntry::make('menu2_count')->alignment(Alignment::End),
                                TextEntry::make('at_cafeteria'),
                                TextEntry::make('notes'),
                            ]),
                    ]), Section::make('Commande')
                    ->schema([
                        Grid::make(2)->schema([
                            IconEntry::make('is_last_meal')
                                ->label('Dernier repas')
                                ->boolean(),
                            TextEntry::make('notes')
                                ->label('Remarques')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array{date: string, soup_count: int, menu1_count: int, menu2_count: int, at_cafeteria: string, notes: string}>
     */
    private static function buildMealsSummary(Order $order): array
    {
        return $order->meals
            ->loadMissing('menus')
            ->sortBy(fn (Meal $meal): string => $meal->date?->format('Y-m-d') ?? '')
            ->values()
            ->map(fn (Meal $meal): array => [
                'date' => Str::title($meal->date?->translatedFormat('l j F Y') ?? ''),
                'soup_count' => (int) $meal->soup_count,
                'menu1_count' => (int) $meal->menus->where('position', 1)->sum('quantity'),
                'menu2_count' => (int) $meal->menus->where('position', 2)->sum('quantity'),
                'at_cafeteria' => $meal->at_cafeteria ? 'Oui' : '',
                'notes' => (string) ($meal->notes ?? ''),
            ])
            ->all();
    }
}
