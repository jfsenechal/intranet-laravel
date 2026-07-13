<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ViewWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\RelationManagers\OrdersRelationManager;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
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
});

function createMealDeliveryClient(string $lastName, bool $isActive): Client
{
    return Client::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => $isActive,
    ]);
}

it('lists active clients with and without an order for the week in one table', function (): void {
    $withOrder = createMealDeliveryClient('WithOrder', isActive: true);
    $withoutOrder = createMealDeliveryClient('WithoutOrder', isActive: true);
    $inactive = createMealDeliveryClient('Inactive', isActive: false);

    Order::create(['week_id' => $this->week->id, 'client_id' => $withOrder->id]);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->week,
        'pageClass' => ViewWeek::class,
    ])
        ->call('loadTable')
        ->assertSee('WithOrder')
        ->assertSee('WithoutOrder')
        ->assertDontSee('Inactive')
        ->assertSee('Détails de la commande')
        ->assertSee('Ajouter une commande');
});

it('renders the sticky-header grid wrapper', function (): void {
    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->week,
        'pageClass' => ViewWeek::class,
    ])
        ->assertSee('meal-week-grid');
});

it('links a client without an order to the create order page for the week', function (): void {
    $withoutOrder = createMealDeliveryClient('WithoutOrder', isActive: true);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->week,
        'pageClass' => ViewWeek::class,
    ])
        ->call('loadTable')
        ->assertSee(CreateOrder::getUrl([
            'week_id' => $this->week->id,
            'client_id' => $withoutOrder->id,
        ]));
});
