<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Pages\ViewDeliveryRoute;
use AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\RelationManagers\ClientsRelationManager;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('displays the client address combining street, number and city', function (): void {
    $route = DeliveryRoute::create(['name' => fake()->unique()->word()]);

    $client = Client::create([
        'last_name' => 'COLLARD',
        'first_name' => 'Christine',
        'street' => 'Rue de la Viorne',
        'number' => '3/11',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => $route->id,
        'is_active' => true,
    ]);

    livewire(ClientsRelationManager::class, [
        'ownerRecord' => $route,
        'pageClass' => ViewDeliveryRoute::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$client])
        ->assertTableColumnStateSet('address', 'Rue de la Viorne 3/11, MARCHE', $client);
});

it('hides inactive and cafeteria clients from the route', function (): void {
    $route = DeliveryRoute::create(['name' => fake()->unique()->word()]);

    $activeClient = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => $route->id,
        'is_active' => true,
    ]);

    $inactiveClient = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => $route->id,
        'is_active' => false,
    ]);

    $cafeteriaClient = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => $route->id,
        'is_active' => true,
        'use_cafeteria' => true,
    ]);

    livewire(ClientsRelationManager::class, [
        'ownerRecord' => $route,
        'pageClass' => ViewDeliveryRoute::class,
    ])
        ->call('loadTable')
        ->assertCanSeeTableRecords([$activeClient])
        ->assertCanNotSeeTableRecords([$inactiveClient, $cafeteriaClient]);
});
