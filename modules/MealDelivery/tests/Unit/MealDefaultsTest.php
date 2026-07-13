<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;

it('creates a meal without an explicit at_cafeteria value', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15']]);

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

    $order = Order::create(['week_id' => $week->id, 'client_id' => $client->id]);

    $meal = Meal::create([
        'order_id' => $order->id,
        'date' => '2026-06-15',
        'soup_count' => 0,
        'notes' => null,
    ]);

    expect($meal->fresh()->at_cafeteria)->toBeFalse();
});
