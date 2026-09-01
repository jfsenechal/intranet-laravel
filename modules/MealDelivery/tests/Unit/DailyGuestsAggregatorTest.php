<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Service\DailyGuestsAggregator;

function createCafeteriaClient(string $lastName, array $attributes = []): Client
{
    return Client::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => true,
        ...$attributes,
    ]);
}

function createGuestReservation(Client $client, string $date, int $menu1 = 0, int $menu2 = 0): GuestReservation
{
    return GuestReservation::create([
        'client_id' => $client->id,
        'date' => $date,
        'menu1_count' => $menu1,
        'menu2_count' => $menu2,
    ]);
}

it('totals the guest meals booked for the day', function (): void {
    createGuestReservation(createCafeteriaClient('HERGOT'), '2026-06-19', menu1: 1);
    createGuestReservation(createCafeteriaClient('DOLCETTE'), '2026-06-19', menu1: 2, menu2: 1);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['totals'])->toBe([
        'clients' => 2,
        'menu1' => 3,
        'menu2' => 1,
        'guests' => 4,
    ]);
});

it('sorts the rows by client last name', function (): void {
    createGuestReservation(createCafeteriaClient('ZANDER'), '2026-06-19', menu1: 1);
    createGuestReservation(createCafeteriaClient('ALBERT'), '2026-06-19', menu1: 1);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toHaveCount(2)
        ->and($result['rows'][0]['client_name'])->toStartWith('ALBERT')
        ->and($result['rows'][1]['client_name'])->toStartWith('ZANDER');
});

it('ignores reservations from other days', function (): void {
    createGuestReservation(createCafeteriaClient('HERGOT'), '2026-06-18', menu1: 5);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toBe([])
        ->and($result['totals']['guests'])->toBe(0);
});

it('skips reservations that carry no meal at all', function (): void {
    createGuestReservation(createCafeteriaClient('HERGOT'), '2026-06-19', menu1: 0, menu2: 0);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toBe([])
        ->and($result['totals']['clients'])->toBe(0);
});

it('carries the address and the notes onto the row', function (): void {
    $client = createCafeteriaClient('HERGOT', ['floor' => '3ème']);
    $reservation = createGuestReservation($client, '2026-06-19', menu1: 2);
    $reservation->update(['notes' => 'Table près de la fenêtre']);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'][0]['address_line'])->toBe('Chaussée de Liège 39/32 3ème')
        ->and($result['rows'][0]['notes'])->toBe('Table près de la fenêtre')
        ->and($result['rows'][0]['total'])->toBe(2);
});
