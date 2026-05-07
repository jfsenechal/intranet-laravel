<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Tables;

use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class WeekInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
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
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, array{date: string, date_url: string, clients_count: int, soup_count: int, menu1_count: int, menu2_count: int}>
     */
    private static function buildDaysSummary(Week $week): array
    {
        $days = collect($week->days ?? [])
            ->map(fn (string $day): string => CarbonImmutable::parse($day)->format('Y-m-d'))
            ->values();

        if ($days->isEmpty()) {
            return [];
        }

        $mealsByDay = Meal::query()
            ->whereIn('date', $days->all())
            ->whereHas('order', fn (Builder $query) => $query->where('week_id', $week->id))
            ->with(['order:id,client_id,week_id', 'menus:id,meal_id,position,quantity'])
            ->get()
            ->groupBy(fn (Meal $meal): string => $meal->date->format('Y-m-d'));

        return $days
            ->map(function (string $day) use ($mealsByDay, $week): array {
                $meals = $mealsByDay->get($day, collect());

                return [
                    'date' => Str::title(CarbonImmutable::parse($day)->translatedFormat('l j F Y')),
                    'date_url' => WeekResource::getUrl('day', [
                        'record' => $week->id,
                        'date' => $day,
                    ]),
                    'clients_count' => $meals->pluck('order.client_id')->unique()->count(),
                    'soup_count' => (int) $meals->sum('soup_count'),
                    'menu1_count' => (int) $meals->sum(
                        fn (Meal $meal): int => (int) $meal->menus->where('position', 1)->sum('quantity'),
                    ),
                    'menu2_count' => (int) $meals->sum(
                        fn (Meal $meal): int => (int) $meal->menus->where('position', 2)->sum('quantity'),
                    ),
                ];
            })
            ->all();
    }
}
