<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\ViewOrder;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

/**
 * A meal row exists for every day of the week; a quantity of 0 makes it a
 * placeholder rather than an ordered day.
 */
function addOrderedMeal(Order $order, string $date, int $quantity, bool $atCafeteria = true): Meal
{
    $meal = Meal::create([
        'date' => $date,
        'soup_count' => 0,
        'order_id' => $order->id,
        'at_cafeteria' => $atCafeteria,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => $quantity,
        'meal_id' => $meal->id,
    ]);

    return $meal;
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15', '2026-06-16'],
    ]);

    $this->client = Client::create([
        'last_name' => 'HERGET',
        'first_name' => 'Anne',
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => true,
    ]);

    $this->order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]);
});

it('books guest meals for the order client on the first ordered day', function (): void {
    // Monday is a placeholder, so Tuesday is the day the form must preselect.
    addOrderedMeal($this->order, '2026-06-15', quantity: 0);
    addOrderedMeal($this->order, '2026-06-16', quantity: 1);

    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->assertActionVisible('add_guest_reservation')
        ->callAction('add_guest_reservation', [
            'menu1_count' => 2,
            'menu2_count' => 1,
        ])
        ->assertHasNoActionErrors();

    $reservation = GuestReservation::query()->firstOrFail();

    expect($reservation->client_id)->toBe($this->client->id)
        ->and($reservation->date->format('Y-m-d'))->toBe('2026-06-16')
        ->and($reservation->menu1_count)->toBe(2)
        ->and($reservation->menu2_count)->toBe(1);
});

it('hides the action for a client who does not eat at the cafeteria', function (): void {
    addOrderedMeal($this->order, '2026-06-15', quantity: 1, atCafeteria: false);
    $this->client->update(['use_cafeteria' => false]);

    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->assertActionHidden('add_guest_reservation');
});

it('hides the action when the order has no ordered day', function (): void {
    addOrderedMeal($this->order, '2026-06-15', quantity: 0);
    addOrderedMeal($this->order, '2026-06-16', quantity: 0);

    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->assertActionHidden('add_guest_reservation');
});

it('refuses a second reservation for the same client on the same day', function (): void {
    addOrderedMeal($this->order, '2026-06-16', quantity: 1);

    GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-16',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->callAction('add_guest_reservation', ['menu1_count' => 3])
        ->assertHasActionErrors(['date']);
});

it('refuses a reservation without a single meal', function (): void {
    addOrderedMeal($this->order, '2026-06-16', quantity: 1);

    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->callAction('add_guest_reservation', [
            'menu1_count' => 0,
            'menu2_count' => 0,
        ])
        ->assertHasActionErrors(['menu1_count']);
});
