<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\Meal;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

final class ClientMonthlyOrdersAggregator
{
    /**
     * @return array{
     *     period: CarbonImmutable,
     *     rows: list<array{date: CarbonImmutable, soup_count: int, menu_1: int, menu_2: int}>,
     *     totals: array{soup: int, menu_1: int, menu_2: int, menus: int}
     * }
     */
    public function build(Client $client, int $month, int $year): array
    {
        $period = CarbonImmutable::create($year, $month, 1);

        /** @var Collection<int, Meal> $meals */
        $meals = Meal::query()
            ->whereHas('order', fn ($query) => $query->where('client_id', $client->id))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with([
                'menus' => fn ($query) => $query->select('id', 'meal_id', 'position', 'quantity'),
            ])
            ->orderBy('date')
            ->get();

        $rows = [];
        $soupTotal = 0;
        $menu1Total = 0;
        $menu2Total = 0;

        foreach ($meals as $meal) {
            $soup = (int) $meal->soup_count;
            $menu1 = (int) $meal->menus->where('position', 1)->sum('quantity');
            $menu2 = (int) $meal->menus->where('position', 2)->sum('quantity');

            $rows[] = [
                'date' => CarbonImmutable::parse($meal->date),
                'soup_count' => $soup,
                'menu_1' => $menu1,
                'menu_2' => $menu2,
            ];

            $soupTotal += $soup;
            $menu1Total += $menu1;
            $menu2Total += $menu2;
        }

        return [
            'period' => $period,
            'rows' => $rows,
            'totals' => [
                'soup' => $soupTotal,
                'menu_1' => $menu1Total,
                'menu_2' => $menu2Total,
                'menus' => $menu1Total + $menu2Total,
            ],
        ];
    }
}
