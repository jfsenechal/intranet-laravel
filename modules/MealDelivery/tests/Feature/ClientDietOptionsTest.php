<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\OrderResource;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\ClientDietOptions;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15', '2026-06-16', '2026-06-17', '2026-06-18', '2026-06-19'],
    ]);

    $this->client = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);

    $this->client->diets()->attach(Diet::create(['name' => 'Sans sel'])->id);
});

it('queries the diets once per client no matter how many meal rows render', function (): void {
    $queries = 0;

    DB::connection('maria-meal-delivery')->listen(function ($query) use (&$queries): void {
        if (str_contains($query->sql, 'diets')) {
            $queries++;
        }
    });

    $this->get(OrderResource::getUrl('create', [
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]))->assertOk();

    expect($queries)->toBe(1);
});

it('memoizes each client separately', function (): void {
    $other = Client::create([
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
    ]);
    $other->diets()->attach(Diet::create(['name' => 'Sans sucre'])->id);

    $options = app(ClientDietOptions::class);

    expect(array_values($options->forClient($this->client->id)))
        ->toBe(['Sans sel'])
        ->and(array_values($options->forClient($other->id)))
        ->toBe(['Sans sucre']);
});

it('resolves a fresh memo per request scope', function (): void {
    $first = app(ClientDietOptions::class);

    expect(array_values($first->forClient($this->client->id)))->toBe(['Sans sel']);

    $this->client->diets()->attach(Diet::create(['name' => 'Sans sucre'])->id);

    app()->forgetScopedInstances();

    expect(array_values(app(ClientDietOptions::class)->forClient($this->client->id)))
        ->toBe(['Sans sel', 'Sans sucre']);
});
