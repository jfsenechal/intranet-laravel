<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\GuestReservation;
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

it('adds guest menus to the total but keeps them out of the diet breakdown', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);

    $delivered = createMeal($week, atCafeteria: false, soupCount: 2, menus: [1 => 3, 2 => 1]);
    $delivered->menus()->where('position', 1)->first()->diets()->attach(Diet::create(['name' => 'Sans sel'])->id);

    GuestReservation::create([
        'client_id' => $delivered->order->client_id,
        'date' => '2026-06-15',
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);

    $summary = (new KitchenExportAggregator())->build($week, '2026-06-15');

    expect($summary['menus_total'])->toBe(7)
        ->and($summary['guests'])->toBe(['menu1' => 2, 'menu2' => 1, 'total' => 3])
        ->and($summary['menus'][0]['total'])->toBe(3)
        ->and($summary['menus'][0]['diets'])->toBe([['label' => 'Sans sel', 'total' => 3]])
        ->and($summary['menus'][1]['total'])->toBe(1)
        ->and($summary['soup_total'])->toBe(2);
});

it('counts guest menus even though guests eat at the cafeteria', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);

    $cafeteria = createMeal($week, atCafeteria: true, soupCount: 4, menus: [1 => 9]);

    GuestReservation::create([
        'client_id' => $cafeteria->order->client_id,
        'date' => '2026-06-15',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    $summary = (new KitchenExportAggregator())->build($week, '2026-06-15');

    expect($summary['menus_total'])->toBe(1)
        ->and($summary['guests']['total'])->toBe(1)
        ->and($summary['menus'][0]['total'])->toBe(0);
});

it('ignores guest reservations booked on another day', function (): void {
    $week = Week::create(['first_day' => '2026-06-15']);

    $delivered = createMeal($week, atCafeteria: false, soupCount: 0, menus: [1 => 2]);

    GuestReservation::create([
        'client_id' => $delivered->order->client_id,
        'date' => '2026-06-16',
        'menu1_count' => 5,
        'menu2_count' => 5,
    ]);

    $summary = (new KitchenExportAggregator())->build($week, '2026-06-15');

    expect($summary['menus_total'])->toBe(2)
        ->and($summary['guests'])->toBe(['menu1' => 0, 'menu2' => 0, 'total' => 0]);
});
