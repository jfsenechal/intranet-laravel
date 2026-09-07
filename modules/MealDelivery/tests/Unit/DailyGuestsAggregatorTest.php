<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use AcMarche\MealDelivery\Service\DailyGuestsAggregator;

function createResident(string $lastName, array $attributes = []): Resident
{
    return Resident::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'is_active' => true,
        ...$attributes,
    ]);
}

function createGuestReservation(Resident $resident, string $date, int $menu1 = 0, int $menu2 = 0): GuestReservation
{
    return GuestReservation::create([
        'resident_id' => $resident->id,
        'date' => $date,
        'menu1_count' => $menu1,
        'menu2_count' => $menu2,
    ]);
}

it('totals the guest meals booked for the day', function (): void {
    createGuestReservation(createResident('HERGOT'), '2026-06-19', menu1: 1);
    createGuestReservation(createResident('DOLCETTE'), '2026-06-19', menu1: 2, menu2: 1);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['totals'])->toBe([
        'residents' => 2,
        'menu1' => 3,
        'menu2' => 1,
        'guests' => 4,
    ]);
});

it('sorts the rows by resident last name', function (): void {
    createGuestReservation(createResident('ZANDER'), '2026-06-19', menu1: 1);
    createGuestReservation(createResident('ALBERT'), '2026-06-19', menu1: 1);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toHaveCount(2)
        ->and($result['rows'][0]['resident_name'])->toStartWith('ALBERT')
        ->and($result['rows'][1]['resident_name'])->toStartWith('ZANDER');
});

it('ignores reservations from other days', function (): void {
    createGuestReservation(createResident('HERGOT'), '2026-06-18', menu1: 5);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toBe([])
        ->and($result['totals']['guests'])->toBe(0);
});

it('skips reservations that carry no meal at all', function (): void {
    createGuestReservation(createResident('HERGOT'), '2026-06-19', menu1: 0, menu2: 0);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'])->toBe([])
        ->and($result['totals']['residents'])->toBe(0);
});

it('carries the room and the notes onto the row', function (): void {
    $resident = createResident('HERGOT', ['room' => '204']);
    $reservation = createGuestReservation($resident, '2026-06-19', menu1: 2);
    $reservation->update(['notes' => 'Table près de la fenêtre']);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'][0]['room'])->toBe('204')
        ->and($result['rows'][0]['notes'])->toBe('Table près de la fenêtre')
        ->and($result['rows'][0]['total'])->toBe(2);
});

it('leaves the room empty rather than blank for a resident without one', function (): void {
    createGuestReservation(createResident('HERGOT', ['room' => null]), '2026-06-19', menu1: 1);

    $result = (new DailyGuestsAggregator())->build('2026-06-19');

    expect($result['rows'][0]['room'])->toBeNull();
});
