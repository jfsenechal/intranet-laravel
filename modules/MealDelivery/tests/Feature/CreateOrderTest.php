<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
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

    $this->client = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'slug' => fake()->unique()->slug(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);
});

it('redirects to the existing order when one already exists for the week and client', function (): void {
    $order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]);

    $this->get(OrderResource::getUrl('create', [
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]))
        ->assertRedirect(OrderResource::getUrl('edit', ['record' => $order]));
});

it('renders the create form when no order exists yet for the week and client', function (): void {
    $this->get(OrderResource::getUrl('create', [
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]))
        ->assertOk();
});

it('does not create a duplicate when the order already exists at submit time', function (): void {
    $order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]);

    livewire(CreateOrder::class)
        ->fillForm([
            'week_id' => $this->week->id,
            'client_id' => $this->client->id,
            'is_last_meal' => false,
            'meals' => [],
        ])
        ->call('create')
        ->assertRedirect(OrderResource::getUrl('edit', ['record' => $order]));

    expect(Order::query()->where('week_id', $this->week->id)->where('client_id', $this->client->id)->count())
        ->toBe(1);
});
