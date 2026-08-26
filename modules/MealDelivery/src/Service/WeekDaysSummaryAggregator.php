<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class WeekDaysSummaryAggregator
{
    /**
     * @return array<int, array{date: string, label: string, clients_count: int, soup_count: int, menu1_count: int, menu2_count: int}>
     */
    public function build(Week $week): array
    {
        $days = collect($week->days ?? [])
            ->map(fn (string $day): CarbonImmutable => CarbonImmutable::parse($day)->startOfDay())
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
            ->map(function (CarbonImmutable $day) use ($mealsByDay): array {
                $meals = $mealsByDay->get($day->format('Y-m-d'), collect());

                return [
                    'date' => $day->format('Y-m-d'),
                    'label' => Str::title($day->translatedFormat('l j F Y')),
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
