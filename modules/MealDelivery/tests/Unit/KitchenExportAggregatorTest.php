<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\KitchenExportAggregator;

function createMeal(Week $week, bool $atCafeteria, int $soupCount, array $menus): Meal
{
    $client = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'slug' => fake()->unique()->slug(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);

    $order = Order::create([
        'week_id' => $week->id,
        'client_id' => $client->id,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => $soupCount,
        'order_id' => $order->id,
        'at_cafeteria' => $atCafeteria,
    ]);

    foreach ($menus as $position => $quantity) {
        Menu::create([
            'position' => $position,
            'quantity' => $quantity,
            'meal_id' => $meal->id,
        ]);
    }

    return $meal;
}

it('excludes cafeteria meals from kitchen totals', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);

    createMeal($week, atCafeteria: false, soupCount: 2, menus: [1 => 3, 2 => 1]);
    createMeal($week, atCafeteria: true, soupCount: 5, menus: [1 => 4, 2 => 4]);

    $summary = (new KitchenExportAggregator())->build($week, '2026-06-15');

    expect($summary['soup_total'])->toBe(2)
        ->and($summary['menus_total'])->toBe(4)
        ->and($summary['menus'][0]['total'])->toBe(3)
        ->and($summary['menus'][1]['total'])->toBe(1);
});

it('aggregates diet labels only for non-cafeteria meals', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);

    $diet = Diet::create(['name' => 'Sans sel']);

    $delivered = createMeal($week, atCafeteria: false, soupCount: 1, menus: [1 => 2]);
    $delivered->menus()->first()->diets()->attach($diet->id);

    $cafeteria = createMeal($week, atCafeteria: true, soupCount: 1, menus: [1 => 9]);
    $cafeteria->menus()->first()->diets()->attach($diet->id);

    $summary = (new KitchenExportAggregator())->build($week, '2026-06-15');

    expect($summary['menus_total'])->toBe(2)
        ->and($summary['menus'][0]['diets'])->toBe([
            ['label' => 'Sans sel', 'total' => 2],
        ]);
});
