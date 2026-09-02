<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ListClients;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $route = DeliveryRoute::create(['name' => fake()->unique()->word()]);

    $makeClient = fn (string $lastName, bool $useCafeteria): Client => Client::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => $route->id,
        'is_active' => true,
        'use_cafeteria' => $useCafeteria,
    ]);

    $this->cafeteriaClient = $makeClient('DOLCETTE', true);
    $this->deliveredClient = $makeClient('LODOMEZ', false);
});

it('lists every active client when the cafeteria filter is off', function (): void {
    livewire(ListClients::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$this->cafeteriaClient, $this->deliveredClient]);
});

it('keeps only the clients eating at the cafeteria when the filter is on', function (): void {
    livewire(ListClients::class)
        ->loadTable()
        ->filterTable('use_cafeteria')
        ->assertCanSeeTableRecords([$this->cafeteriaClient])
        ->assertCanNotSeeTableRecords([$this->deliveredClient]);
});
