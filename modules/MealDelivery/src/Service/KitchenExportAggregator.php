<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

final class KitchenExportAggregator
{
    private const string NORMAL_LABEL = 'normal';

    /**
     * Guest meals carry no diet, so they stay out of the per-position breakdown —
     * whose total must keep matching the sum of its diet lines — and are reported
     * separately in `guests`. Only `menus_total` covers both.
     *
     * @return array{
     *     date: CarbonImmutable,
     *     soup_total: int,
     *     menus_total: int,
     *     menus: array<int, array{
     *         position: int,
     *         total: int,
     *         diets: array<int, array{label: string, total: int}>
     *     }>,
     *     guests: array{menu1: int, menu2: int, total: int}
     * }
     */
    public function build(Week $week, string $date): array
    {
        $dateCarbon = CarbonImmutable::parse($date);

        $meals = Meal::query()
            ->whereDate('date', $dateCarbon->format('Y-m-d'))
            ->where('at_cafeteria', false)
            ->whereHas('order', fn (Builder $query) => $query->where('week_id', $week->id))
            ->with(['menus.diets:id,name'])
            ->get();

        $soupTotal = (int) $meals->sum('soup_count');

        $menus = [1, 2];
        $byPosition = [];
        $grandTotal = 0;

        foreach ($menus as $position) {
            $positionTotal = 0;
            $byDiet = [];

            foreach ($meals as $meal) {
                foreach ($meal->menus->where('position', $position) as $menu) {
                    $quantity = (int) $menu->quantity;

                    if ($quantity === 0) {
                        continue;
                    }

                    $positionTotal += $quantity;

                    $labels = $menu->diets->isEmpty()
                        ? [self::NORMAL_LABEL]
                        : $menu->diets->pluck('name')->all();

                    foreach ($labels as $label) {
                        $byDiet[$label] = ($byDiet[$label] ?? 0) + $quantity;
                    }
                }
            }

            ksort($byDiet);

            $byPosition[] = [
                'position' => $position,
                'total' => $positionTotal,
                'diets' => array_values(array_map(
                    fn (string $label, int $total): array => ['label' => $label, 'total' => $total],
                    array_keys($byDiet),
                    array_values($byDiet),
                )),
            ];

            $grandTotal += $positionTotal;
        }

        $guests = self::guestTotals($dateCarbon);

        return [
            'date' => $dateCarbon,
            'soup_total' => $soupTotal,
            'menus_total' => $grandTotal + $guests['total'],
            'menus' => $byPosition,
            'guests' => $guests,
        ];
    }

    /**
     * Guests eat at the cafeteria, but the kitchen still cooks their menus.
     *
     * @return array{menu1: int, menu2: int, total: int}
     */
    private static function guestTotals(CarbonImmutable $date): array
    {
        $totals = (new DailyGuestsAggregator())->build($date->format('Y-m-d'))['totals'];

        return [
            'menu1' => $totals['menu1'],
            'menu2' => $totals['menu2'],
            'total' => $totals['guests'],
        ];
    }
}
