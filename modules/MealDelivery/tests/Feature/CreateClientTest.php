<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\CreateClient;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('requires a delivery route when creating a client', function (): void {
    livewire(CreateClient::class)
        ->fillForm([
            'last_name' => 'COLLARD',
            'first_name' => 'Christine',
            'street' => 'Rue de la Viorne',
            'number' => '3/11',
            'postal_code' => 6900,
            'city' => 'MARCHE',
            'route_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['route_id' => 'required']);
});

it('creates a client with a delivery route', function (): void {
    $route = DeliveryRoute::create(['name' => fake()->unique()->word()]);

    livewire(CreateClient::class)
        ->fillForm([
            'last_name' => 'COLLARD',
            'first_name' => 'Christine',
            'street' => 'Rue de la Viorne',
            'number' => '3/11',
            'postal_code' => 6900,
            'city' => 'MARCHE',
            'route_id' => $route->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Client::class, [
        'last_name' => 'COLLARD',
        'first_name' => 'Christine',
        'route_id' => $route->id,
    ]);
});
