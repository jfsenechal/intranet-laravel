<?php

declare(strict_types=1);

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
        'slug' => fake()->unique()->slug(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => $isActive,
    ]);
}

it('scopes to active clients without an order for the week', function (): void {
    $withoutOrder = createMealDeliveryClient('WithoutOrder', isActive: true);
    $withOrder = createMealDeliveryClient('WithOrder', isActive: true);
    $inactive = createMealDeliveryClient('Inactive', isActive: false);

    Order::create(['week_id' => $this->week->id, 'client_id' => $withOrder->id]);

    $clients = Client::query()->activeWithoutOrderForWeek($this->week)->get();

    expect($clients->pluck('id')->all())->toBe([$withoutOrder->id]);
});

it('ignores orders that belong to another week', function (): void {
    $client = createMealDeliveryClient('Other', isActive: true);
    $otherWeek = Week::create(['first_day' => '2026-06-22', 'days' => ['2026-06-22']]);

    Order::create(['week_id' => $otherWeek->id, 'client_id' => $client->id]);

    $clients = Client::query()->activeWithoutOrderForWeek($this->week)->get();

    expect($clients->pluck('id')->all())->toBe([$client->id]);
});

it('searches orders by client last and first name', function (): void {
    $piette = createMealDeliveryClient('Piette', isActive: true);
    $dupont = createMealDeliveryClient('Dupont', isActive: true);

    $pietteOrder = Order::create(['week_id' => $this->week->id, 'client_id' => $piette->id]);
    $dupontOrder = Order::create(['week_id' => $this->week->id, 'client_id' => $dupont->id]);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->week,
        'pageClass' => ViewWeek::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$pietteOrder, $dupontOrder])
        ->searchTable('Piett')
        ->assertCanSeeTableRecords([$pietteOrder])
        ->assertCanNotSeeTableRecords([$dupontOrder]);
});

it('shows the count of clients without an order in the header action', function (): void {
    createMealDeliveryClient('First', isActive: true);
    createMealDeliveryClient('Second', isActive: true);
    $withOrder = createMealDeliveryClient('Covered', isActive: true);
    createMealDeliveryClient('Inactive', isActive: false);

    Order::create(['week_id' => $this->week->id, 'client_id' => $withOrder->id]);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->week,
        'pageClass' => ViewWeek::class,
    ])
        ->assertSee('Clients sans commande (2)');
});
