<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\Resident;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Guest meals per resident over a month, used to extract the billing counts that
 * were previously compiled by hand from care+. Residents never pay for their own
 * meal, so every count here is a guest meal.
 */
final class MonthlyGuestsAggregator
{
    /**
     * @return array{
     *     period: CarbonImmutable,
     *     rows: list<array{
     *         resident: Resident,
     *         menu1_total: int,
     *         menu2_total: int,
     *         guests_total: int,
     *     }>,
     *     totals: array{menu1: int, menu2: int, guests: int},
     * }
     */
    public function build(int $month, int $year): array
    {
        $period = CarbonImmutable::create($year, $month, 1);

        $residents = Resident::query()
            ->whereHas(
                'guestReservations',
                fn (Builder $query) => $query
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month),
            )
            ->with([
                'guestReservations' => fn ($query) => $query
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $rows = [];
        $menu1Total = 0;
        $menu2Total = 0;

        foreach ($residents as $resident) {
            $menu1 = (int) $resident->guestReservations->sum('menu1_count');
            $menu2 = (int) $resident->guestReservations->sum('menu2_count');

            if ($menu1 + $menu2 === 0) {
                continue;
            }

            $rows[] = [
                'resident' => $resident,
                'menu1_total' => $menu1,
                'menu2_total' => $menu2,
                'guests_total' => $menu1 + $menu2,
            ];

            $menu1Total += $menu1;
            $menu2Total += $menu2;
        }

        return [
            'period' => $period,
            'rows' => $rows,
            'totals' => [
                'menu1' => $menu1Total,
                'menu2' => $menu2Total,
                'guests' => $menu1Total + $menu2Total,
            ],
        ];
    }
}
