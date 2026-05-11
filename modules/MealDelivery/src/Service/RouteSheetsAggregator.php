<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Service;

use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\RouteOrder;
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
                'order.client.routeOrders',
                'menus.diets:id,name',
            ])
            ->get();

        $routePositions = RouteOrder::query()
            ->get(['client_id', 'route_id', 'position'])
            ->keyBy(fn (RouteOrder $routeOrder): string => $routeOrder->client_id.':'.$routeOrder->route_id);

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

            $row = self::buildRow($meal);
            $sortPosition = PHP_INT_MAX;

            if ($meal->at_cafeteria) {
                $cafeteriaRows[] = ['row' => $row, 'position' => $sortPosition, 'last_name' => $client->last_name];

                continue;
            }

            $routeId = (int) ($client->route_id ?? 0);
            if ($routeId === 0 || ! isset($routeBuckets[$routeId])) {
                continue;
            }

            $override = $routePositions->get($client->id.':'.$routeId);
            if ($override !== null) {
                $sortPosition = (int) $override->position;
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
     * }
     */
    private static function buildRow(Meal $meal): array
    {
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
        ];
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
