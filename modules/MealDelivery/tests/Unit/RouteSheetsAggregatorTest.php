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
