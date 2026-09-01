<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use AcMarche\MealDelivery\Filament\Resources\Clients\RelationManagers\GuestReservationsRelationManager;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\ListGuestReservations;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->client = Client::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'street' => 'Chaussée de Liège',
        'number' => '39/11',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => true,
    ]);

    $this->first = GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-19',
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);

    $this->second = GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-20',
        'menu1_count' => 1,
        'menu2_count' => 3,
    ]);
});

it('lists the guest reservations', function (): void {
    livewire(ListGuestReservations::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$this->first, $this->second]);
});

it('sums the expected head count across the table', function (): void {
    livewire(ListGuestReservations::class)
        ->loadTable()
        ->assertTableColumnSummarySet('menu1_count', 'sum', 3)
        ->assertTableColumnSummarySet('menu2_count', 'sum', 4)
        ->assertTableColumnSummarySet('total', 'sum', 7);
});

it('lists a client own guest reservations under their page', function (): void {
    livewire(GuestReservationsRelationManager::class, [
        'ownerRecord' => $this->client,
        'pageClass' => ViewClient::class,
    ])
        ->loadTable()
        ->assertCanSeeTableRecords([$this->first, $this->second]);
});

it('hides the guest reservations of a client who does not eat at the cafeteria', function (): void {
    $this->client->update(['use_cafeteria' => false]);

    expect(GuestReservationsRelationManager::canViewForRecord($this->client, ViewClient::class))
        ->toBeFalse();
});
