<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class RouteSheetsAggregator
{
    /**
     * @return array{
     *     date: CarbonImmutable,
     *     routes: array<int, array{
     *         id: int,
     *         name: string,
     *         rows: list<array{
     *             client_name: string,
     *             phone: ?string,
     *             address_line: string,
     *             city_line: string,
     *             soup: int,
     *             menu1: int,
     *             menu1_diets: list<string>,
     *             menu2: int,
     *             menu2_diets: list<string>,
     *             notes: ?string,
     *             client_notes: ?string,
     *             new_sheet: bool,
     *             take_back_sheet: bool,
     *             disposable_recipient: bool,
     *         }>,
     *         totals: array{clients: int, soup: int, menu1: int, menu2: int, menus_total: int},
     *     }>,
     *     cafeteria: array{
     *         name: string,
     *         rows: list<array{
     *             client_name: string,
     *             phone: ?string,
     *             address_line: string,
     *             city_line: string,
     *             soup: int,
     *             menu1: int,
     *             menu1_diets: list<string>,
     *             menu2: int,
     *             menu2_diets: list<string>,
     *             notes: ?string,
     *             client_notes: ?string,
     *             new_sheet: bool,
     *             take_back_sheet: bool,
     *             disposable_recipient: bool,
     *         }>,
     *         totals: array{clients: int, soup: int, menu1: int, menu2: int, menus_total: int},
     *     }
     * }
     */
    public function build(Week $week, string $date): array
    {
        $dateCarbon = CarbonImmutable::parse($date);

        $meals = Meal::query()
            ->whereDate('date', $dateCarbon->format('Y-m-d'))
            ->whereHas('order', fn (Builder $query) => $query->where('week_id', $week->id))
            ->with([
                'order.client.deliveryRoute',
                'menus.diets:id,name',
            ])
            ->get();

        $dateString = $dateCarbon->format('Y-m-d');
        $orderIds = $meals->pluck('order_id')->unique()->values()->all();
        $clientIds = $meals->pluck('order.client_id')->filter()->unique()->values()->all();

        $orderDateRanges = self::orderDateRanges($orderIds);
        $clientsWithNextWeekOrder = self::clientsWithNextWeekOrder($week, $clientIds, $dateString);
        // ISO weekday: Monday = 1 … Sunday = 7, so Wednesday or later means >= 3.
        $isEqualOrGreaterThanWednesday = $dateCarbon->isoWeekday() >= 3;

        $routes = DeliveryRoute::query()->orderBy('id')->get(['id', 'name']);

        $routeBuckets = [];
        foreach ($routes as $route) {
            $routeBuckets[$route->id] = [
                'id' => (int) $route->id,
                'name' => (string) $route->name,
                'rows' => [],
            ];
        }

        $cafeteriaRows = [];

        foreach ($meals as $meal) {
            $client = $meal->order?->client;

            if (! $client) {
                continue;
            }

            $range = $orderDateRanges->get($meal->order_id);
            $newSheet = $range !== null && $dateString === $range['min_date'];
            $disposableRecipient = (bool) $meal->order->is_last_meal
                && $range !== null
                && $dateString === $range['max_date'];
            $takeBackSheet = $isEqualOrGreaterThanWednesday
                && ! isset($clientsWithNextWeekOrder[$client->id]);

            $row = self::buildRow($meal, $newSheet, $takeBackSheet, $disposableRecipient);

            if ($row['menu1'] === 0 && $row['menu2'] === 0) {
                continue;
            }

            $sortPosition = PHP_INT_MAX;

            if ($meal->at_cafeteria) {
                $cafeteriaRows[] = ['row' => $row, 'position' => $sortPosition, 'last_name' => $client->last_name];

                continue;
            }

            $routeId = (int) ($client->route_id ?? 0);
            if ($routeId === 0 || ! isset($routeBuckets[$routeId])) {
                continue;
            }

            if ($client->route_position !== null) {
                $sortPosition = (int) $client->route_position;
            }

            $routeBuckets[$routeId]['rows'][] = [
                'row' => $row,
                'position' => $sortPosition,
                'last_name' => $client->last_name,
            ];
        }

        $routesOutput = [];
        foreach ($routeBuckets as $bucket) {
            $sortedRows = self::sortRows($bucket['rows']);
            $routesOutput[] = [
                'id' => $bucket['id'],
                'name' => $bucket['name'],
                'rows' => $sortedRows,
                'totals' => self::computeTotals($sortedRows),
            ];
        }

        $cafeteriaSorted = self::sortRows($cafeteriaRows);

        return [
            'date' => $dateCarbon,
            'routes' => $routesOutput,
            'cafeteria' => [
                'name' => 'Cafétéria',
                'rows' => $cafeteriaSorted,
                'totals' => self::computeTotals($cafeteriaSorted),
            ],
        ];
    }

    /**
     * @return array{
     *     client_name: string,
     *     phone: ?string,
     *     address_line: string,
     *     city_line: string,
     *     soup: int,
     *     menu1: int,
     *     menu1_diets: list<string>,
     *     menu2: int,
     *     menu2_diets: list<string>,
     *     notes: ?string,
     *     client_notes: ?string,
     *     new_sheet: bool,
     *     take_back_sheet: bool,
     *     disposable_recipient: bool,
     * }
     */
    private static function buildRow(
        Meal $meal,
        bool $newSheet,
        bool $takeBackSheet,
        bool $disposableRecipient,
    ): array {
        $client = $meal->order->client;

        $menu1 = self::menuFor($meal, 1);
        $menu2 = self::menuFor($meal, 2);

        $floor = mb_trim((string) ($client->floor ?? ''));
        $addressLine = mb_trim($client->street.' '.$client->number);
        if ($floor !== '') {
            $addressLine .= ' '.$floor;
        }

        return [
            'client_name' => mb_trim($client->last_name.' '.$client->first_name),
            'phone' => $client->phone,
            'address_line' => $addressLine,
            'city_line' => mb_trim($client->postal_code.' '.$client->city),
            'soup' => (int) $meal->soup_count,
            'menu1' => $menu1['quantity'],
            'menu1_diets' => $menu1['diets'],
            'menu2' => $menu2['quantity'],
            'menu2_diets' => $menu2['diets'],
            'notes' => $meal->notes !== null && mb_trim((string) $meal->notes) !== '' ? (string) $meal->notes : null,
            'client_notes' => $client->notes !== null && mb_trim((string) $client->notes) !== '' ? (string) $client->notes : null,
            'new_sheet' => $newSheet,
            'take_back_sheet' => $takeBackSheet,
            'disposable_recipient' => $disposableRecipient,
        ];
    }

    /**
     * Earliest and latest meal date for each order, keyed by order id.
     *
     * @param  list<int>  $orderIds
     * @return Collection<int, array{min_date: string, max_date: string}>
     */
    private static function orderDateRanges(array $orderIds): Collection
    {
        if ($orderIds === []) {
            return new Collection();
        }

        return Meal::query()
            ->whereIn('order_id', $orderIds)
            ->selectRaw('order_id, MIN(date) as min_date, MAX(date) as max_date')
            ->groupBy('order_id')
            ->get()
            ->mapWithKeys(fn (Meal $meal): array => [
                (int) $meal->order_id => [
                    'min_date' => CarbonImmutable::parse((string) $meal->min_date)->format('Y-m-d'),
                    'max_date' => CarbonImmutable::parse((string) $meal->max_date)->format('Y-m-d'),
                ],
            ]);
    }

    /**
     * Clients who already have a meal scheduled after the current week, keyed by
     * client id for O(1) lookups. Mirrors the legacy `hasCommandNextWeek` check.
     *
     * @param  list<int>  $clientIds
     * @return array<int, true>
     */
    private static function clientsWithNextWeekOrder(Week $week, array $clientIds, string $dateString): array
    {
        if ($clientIds === []) {
            return [];
        }

        $lastDay = collect($week->days ?? [])
            ->filter()
            ->map(fn ($day): string => CarbonImmutable::parse((string) $day)->format('Y-m-d'))
            ->push(CarbonImmutable::parse($week->first_day)->format('Y-m-d'))
            ->push($dateString)
            ->max();

        return Order::query()
            ->whereIn('client_id', $clientIds)
            ->whereHas('meals', fn (Builder $query) => $query->whereDate('date', '>', $lastDay))
            ->pluck('client_id')
            ->flip()
            ->map(fn (): bool => true)
            ->all();
    }

    /**
     * @return array{quantity: int, diets: list<string>}
     */
    private static function menuFor(Meal $meal, int $position): array
    {
        $quantity = 0;
        $diets = [];

        foreach ($meal->menus->where('position', $position) as $menu) {
            $quantity += (int) $menu->quantity;
            foreach ($menu->diets as $diet) {
                $diets[] = (string) $diet->name;
            }
        }

        return [
            'quantity' => $quantity,
            'diets' => array_values(array_unique($diets)),
        ];
    }

    /**
     * @param  list<array{row: array<string, mixed>, position: int, last_name: string}>  $rows
     * @return list<array<string, mixed>>
     */
    private static function sortRows(array $rows): array
    {
        return collect($rows)
            ->sortBy([
                ['position', 'asc'],
                ['last_name', 'asc'],
            ])
            ->pluck('row')
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{clients: int, soup: int, menu1: int, menu2: int, menus_total: int}
     */
    private static function computeTotals(array $rows): array
    {
        $collection = new Collection($rows);

        $soup = (int) $collection->sum('soup');
        $menu1 = (int) $collection->sum('menu1');
        $menu2 = (int) $collection->sum('menu2');

        return [
            'clients' => $collection->count(),
            'soup' => $soup,
            'menu1' => $menu1,
            'menu2' => $menu2,
            'menus_total' => $menu1 + $menu2,
        ];
    }
}
