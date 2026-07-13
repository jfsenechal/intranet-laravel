<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\CafeteriaSheet;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15'],
    ]);

    $cafeteriaClient = Client::create([
        'last_name' => 'BOON',
        'first_name' => 'Micheline',
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);

    $order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $cafeteriaClient->id,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 1,
        'order_id' => $order->id,
        'at_cafeteria' => true,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => 1,
        'meal_id' => $meal->id,
    ]);
});

it('renders the cafeteria sheet for a given day with its clients', function (): void {
    livewire(CafeteriaSheet::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertSee('BOON Micheline')
        ->assertSee('Cafétariat');
});
