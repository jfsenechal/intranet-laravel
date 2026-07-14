<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\RouteSheetsAggregator;

function createRouteMeal(Week $week, DeliveryRoute $route, ?string $clientNotes, ?string $mealNotes): Meal
{
    $client = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => $route->id,
        'notes' => $clientNotes,
        'is_active' => true,
    ]);

    $order = Order::create([
        'week_id' => $week->id,
        'client_id' => $client->id,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 1,
        'order_id' => $order->id,
        'at_cafeteria' => false,
        'notes' => $mealNotes,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => 1,
        'meal_id' => $meal->id,
    ]);

    return $meal;
}

function makeRouteClient(DeliveryRoute $route): Client
{
    return Client::create([
        'last_name' => fake()->unique()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => $route->id,
        'is_active' => true,
    ]);
}

function addRouteMeal(Order $order, string $date): Meal
{
    $meal = Meal::create([
        'date' => $date,
        'soup_count' => 1,
        'order_id' => $order->id,
        'at_cafeteria' => false,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => 1,
        'meal_id' => $meal->id,
    ]);

    return $meal;
}

it('flags the new sheet on the first meal of the week only', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15', '2026-06-17', '2026-06-19']]);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);
    $client = makeRouteClient($route);
    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id]);

    addRouteMeal($order, '2026-06-15');
    addRouteMeal($order, '2026-06-17');

    $monday = (new RouteSheetsAggregator())->build($week, '2026-06-15')['routes'][0]['rows'][0];
    $wednesday = (new RouteSheetsAggregator())->build($week, '2026-06-17')['routes'][0]['rows'][0];

    expect($monday['new_sheet'])->toBeTrue()
        ->and($wednesday['new_sheet'])->toBeFalse();
});

it('flags take back sheet from Wednesday when no order exists next week', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15', '2026-06-17']]);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);
    $client = makeRouteClient($route);
    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id]);

    addRouteMeal($order, '2026-06-15');
    addRouteMeal($order, '2026-06-17');

    $monday = (new RouteSheetsAggregator())->build($week, '2026-06-15')['routes'][0]['rows'][0];
    $wednesday = (new RouteSheetsAggregator())->build($week, '2026-06-17')['routes'][0]['rows'][0];

    expect($monday['take_back_sheet'])->toBeFalse()
        ->and($wednesday['take_back_sheet'])->toBeTrue();
});

it('does not take back the sheet when the client has an order next week', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15', '2026-06-17']]);
    $nextWeek = Week::create(['first_day' => '2026-06-22', 'days' => ['2026-06-24']]);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);
    $client = makeRouteClient($route);

    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id]);
    addRouteMeal($order, '2026-06-17');

    $nextOrder = Order::create(['week_id' => $nextWeek->id, 'client_id' => $client->id]);
    addRouteMeal($nextOrder, '2026-06-24');

    $wednesday = (new RouteSheetsAggregator())->build($week, '2026-06-17')['routes'][0]['rows'][0];

    expect($wednesday['take_back_sheet'])->toBeFalse();
});

it('flags a disposable recipient on the last meal of a closing order only', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15', '2026-06-17']]);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);
    $client = makeRouteClient($route);
    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id, 'is_last_meal' => true]);

    addRouteMeal($order, '2026-06-15');
    addRouteMeal($order, '2026-06-17');

    $monday = (new RouteSheetsAggregator())->build($week, '2026-06-15')['routes'][0]['rows'][0];
    $wednesday = (new RouteSheetsAggregator())->build($week, '2026-06-17')['routes'][0]['rows'][0];

    expect($monday['disposable_recipient'])->toBeFalse()
        ->and($wednesday['disposable_recipient'])->toBeTrue();
});

it('does not flag a disposable recipient when the order is not a last meal', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15', '2026-06-17']]);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);
    $client = makeRouteClient($route);
    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id, 'is_last_meal' => false]);

    addRouteMeal($order, '2026-06-15');
    addRouteMeal($order, '2026-06-17');

    $wednesday = (new RouteSheetsAggregator())->build($week, '2026-06-17')['routes'][0]['rows'][0];

    expect($wednesday['disposable_recipient'])->toBeFalse();
});

it('exposes both client notes and meal notes on the route sheet rows', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);

    createRouteMeal($week, $route, clientNotes: 'Sonner fort', mealNotes: 'Sans sel');

    $sheets = (new RouteSheetsAggregator())->build($week, '2026-06-15');

    $row = $sheets['routes'][0]['rows'][0];

    expect($row['client_notes'])->toBe('Sonner fort')
        ->and($row['notes'])->toBe('Sans sel');
});

it('excludes clients whose meal has no menu1 and no menu2', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);

    $withMenu = createRouteMeal($week, $route, clientNotes: null, mealNotes: null);

    $client = Client::create([
        'last_name' => 'Sansmenu',
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => $route->id,
        'is_active' => true,
    ]);

    $order = Order::create([
        'week_id' => $week->id,
        'client_id' => $client->id,
    ]);

    Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 1,
        'order_id' => $order->id,
        'at_cafeteria' => false,
    ]);

    $sheets = (new RouteSheetsAggregator())->build($week, '2026-06-15');

    $names = collect($sheets['routes'][0]['rows'])->pluck('client_name');

    expect($sheets['routes'][0]['rows'])->toHaveCount(1)
        ->and($names)->toContain($withMenu->order->client->last_name.' '.$withMenu->order->client->first_name)
        ->and($names->contains(fn (string $name): bool => str_contains($name, 'Sansmenu')))->toBeFalse();
});

it('returns null notes when client and meal have none', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);
    $route = DeliveryRoute::create(['name' => 'Tournée 1']);

    createRouteMeal($week, $route, clientNotes: null, mealNotes: null);

    $sheets = (new RouteSheetsAggregator())->build($week, '2026-06-15');

    $row = $sheets['routes'][0]['rows'][0];

    expect($row['client_notes'])->toBeNull()
        ->and($row['notes'])->toBeNull();
});
