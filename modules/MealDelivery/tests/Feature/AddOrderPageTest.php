<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Weeks\WeekResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15'],
    ]);

    $this->route = DeliveryRoute::create(['name' => 'Tournée 1']);
});

function makePickerClient(DeliveryRoute $route, string $lastName, bool $isActive): Client
{
    return Client::create([
        'last_name' => $lastName,
        'first_name' => 'Test',
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => $route->id,
        'is_active' => $isActive,
    ]);
}

it('lists active clients without an order, grouped by route, linking to create order', function (): void {
    $free = makePickerClient($this->route, 'Libreclient', isActive: true);
    $ordered = makePickerClient($this->route, 'Servidejaclient', isActive: true);
    $inactive = makePickerClient($this->route, 'Inactifclient', isActive: false);

    Order::create(['week_id' => $this->week->id, 'client_id' => $ordered->id]);

    $this->get(WeekResource::getUrl('add-order', ['record' => $this->week]))
        ->assertOk()
        ->assertSee('Tournée 1')
        ->assertSee('Libreclient')
        ->assertSee(CreateOrder::getUrl([
            'week_id' => $this->week->id,
            'client_id' => $free->id,
        ]))
        ->assertDontSee('Servidejaclient')
        ->assertDontSee('Inactifclient');
});

it('shows an empty message when every active client already has an order', function (): void {
    $ordered = makePickerClient($this->route, 'Seulclient', isActive: true);
    Order::create(['week_id' => $this->week->id, 'client_id' => $ordered->id]);

    $this->get(WeekResource::getUrl('add-order', ['record' => $this->week]))
        ->assertOk()
        ->assertSee('Tous les clients actifs ont déjà une commande pour cette semaine.')
        ->assertDontSee('Seulclient');
});
