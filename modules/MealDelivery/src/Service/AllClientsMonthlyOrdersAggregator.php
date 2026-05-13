<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Client;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class AllClientsMonthlyOrdersAggregator
{
    /**
     * @return array{
     *     period: CarbonImmutable,
     *     rows: list<array{client: Client, soup_total: int, menus_total: int}>,
     *     totals: array{soup: int, menus: int}
     * }
     */
    public function build(int $month, int $year): array
    {
        $period = CarbonImmutable::create($year, $month, 1);

        $clients = Client::query()
            ->whereHas(
                'orders.meals',
                fn (Builder $query) => $query
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month),
            )
            ->with([
                'orders.meals' => fn ($query) => $query
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month),
                'orders.meals.menus' => fn ($query) => $query->select('id', 'meal_id', 'quantity'),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = [];
        $soupTotal = 0;
        $menusTotal = 0;

        foreach ($clients as $client) {
            $meals = $client->orders->flatMap(fn ($order) => $order->meals);
            $soup = (int) $meals->sum('soup_count');
            $menus = (int) $meals->flatMap(fn ($meal) => $meal->menus)->sum('quantity');

            $rows[] = [
                'client' => $client,
                'soup_total' => $soup,
                'menus_total' => $menus,
            ];

            $soupTotal += $soup;
            $menusTotal += $menus;
        }

        return [
            'period' => $period,
            'rows' => $rows,
            'totals' => [
                'soup' => $soupTotal,
                'menus' => $menusTotal,
            ],
        ];
    }
}
