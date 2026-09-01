<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Service\MonthlyGuestsAggregator;

function createClientWithGuestReservations(string $lastName, array $reservations): Client
{
    $client = Client::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => true,
    ]);

    foreach ($reservations as $date => $counts) {
        GuestReservation::create([
            'client_id' => $client->id,
            'date' => $date,
            'menu1_count' => $counts[0],
            'menu2_count' => $counts[1],
        ]);
    }

    return $client;
}

it('sums the guest meals of each client over the month', function (): void {
    createClientWithGuestReservations('HERGOT', [
        '2026-06-05' => [1, 0],
        '2026-06-19' => [2, 1],
    ]);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['rows'])->toHaveCount(1)
        ->and($result['rows'][0]['menu1_total'])->toBe(3)
        ->and($result['rows'][0]['menu2_total'])->toBe(1)
        ->and($result['rows'][0]['guests_total'])->toBe(4)
        ->and($result['totals'])->toBe(['menu1' => 3, 'menu2' => 1, 'guests' => 4]);
});

it('excludes reservations outside the requested month', function (): void {
    createClientWithGuestReservations('HERGOT', [
        '2026-05-31' => [4, 0],
        '2026-07-01' => [5, 0],
        '2026-06-15' => [1, 0],
    ]);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['totals']['guests'])->toBe(1);
});

it('excludes clients without any guest meal in the month', function (): void {
    createClientWithGuestReservations('SANSINVITE', []);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['rows'])->toBe([])
        ->and($result['totals']['guests'])->toBe(0);
});

it('orders clients by last name', function (): void {
    createClientWithGuestReservations('ZANDER', ['2026-06-10' => [1, 0]]);
    createClientWithGuestReservations('ALBERT', ['2026-06-10' => [1, 0]]);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['rows'][0]['client']->last_name)->toBe('ALBERT')
        ->and($result['rows'][1]['client']->last_name)->toBe('ZANDER');
});
