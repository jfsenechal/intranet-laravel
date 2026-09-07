<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use AcMarche\MealDelivery\Service\MonthlyGuestsAggregator;

function createResidentWithGuestReservations(string $lastName, array $reservations): Resident
{
    $resident = Resident::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'is_active' => true,
    ]);

    foreach ($reservations as $date => $counts) {
        GuestReservation::create([
            'resident_id' => $resident->id,
            'date' => $date,
            'menu1_count' => $counts[0],
            'menu2_count' => $counts[1],
        ]);
    }

    return $resident;
}

it('sums the guest meals of each resident over the month', function (): void {
    createResidentWithGuestReservations('HERGOT', [
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
    createResidentWithGuestReservations('HERGOT', [
        '2026-05-31' => [4, 0],
        '2026-07-01' => [5, 0],
        '2026-06-15' => [1, 0],
    ]);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['totals']['guests'])->toBe(1);
});

it('excludes residents without any guest meal in the month', function (): void {
    createResidentWithGuestReservations('SANSINVITE', []);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['rows'])->toBe([])
        ->and($result['totals']['guests'])->toBe(0);
});

it('orders residents by last name', function (): void {
    createResidentWithGuestReservations('ZANDER', ['2026-06-10' => [1, 0]]);
    createResidentWithGuestReservations('ALBERT', ['2026-06-10' => [1, 0]]);

    $result = (new MonthlyGuestsAggregator())->build(6, 2026);

    expect($result['rows'][0]['resident']->last_name)->toBe('ALBERT')
        ->and($result['rows'][1]['resident']->last_name)->toBe('ZANDER');
});
