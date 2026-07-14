<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use AcMarche\MealDelivery\Filament\Resources\Clients\RelationManagers\OrdersRelationManager;
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

    $this->client = Client::create([
        'last_name' => 'Dupont',
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);
});

it('lists every order of the client on the view page relation manager', function (): void {
    $firstWeek = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15']]);
    $secondWeek = Week::create(['first_day' => '2026-06-22', 'days' => ['2026-06-22']]);

    $firstOrder = Order::create(['week_id' => $firstWeek->id, 'client_id' => $this->client->id]);
    $secondOrder = Order::create(['week_id' => $secondWeek->id, 'client_id' => $this->client->id]);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->client,
        'pageClass' => ViewClient::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$firstOrder, $secondOrder]);
});

it('excludes orders belonging to another client', function (): void {
    $week = Week::create(['first_day' => '2026-06-15', 'days' => ['2026-06-15']]);

    $ownOrder = Order::create(['week_id' => $week->id, 'client_id' => $this->client->id]);

    $otherClient = Client::create([
        'last_name' => 'Martin',
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);
    $otherOrder = Order::create(['week_id' => $week->id, 'client_id' => $otherClient->id]);

    livewire(OrdersRelationManager::class, [
        'ownerRecord' => $this->client,
        'pageClass' => ViewClient::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$ownOrder])
        ->assertCanNotSeeTableRecords([$otherOrder]);
});
