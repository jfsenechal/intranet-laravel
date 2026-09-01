<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\CreateGuestReservation;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\EditGuestReservation;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

function makeClient(string $lastName, bool $useCafeteria = true): Client
{
    return Client::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => $useCafeteria,
    ]);
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->client = makeClient('DOLCETTE');
});

it('creates a guest reservation split over the two menus', function (): void {
    livewire(CreateGuestReservation::class)
        ->fillForm([
            'client_id' => $this->client->id,
            'date' => '2026-06-19',
            'menu1_count' => 2,
            'menu2_count' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(GuestReservation::class, [
        'client_id' => $this->client->id,
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);
});

it('rejects a reservation without a single meal', function (): void {
    livewire(CreateGuestReservation::class)
        ->fillForm([
            'client_id' => $this->client->id,
            'date' => '2026-06-19',
            'menu1_count' => 0,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['menu1_count']);
});

it('rejects a second reservation for the same client on the same day', function (): void {
    GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-19',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(CreateGuestReservation::class)
        ->fillForm([
            'client_id' => $this->client->id,
            'date' => '2026-06-19',
            'menu1_count' => 3,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['date']);
});

it('allows the same day for two different clients', function (): void {
    $other = makeClient('HERGOT');

    GuestReservation::create([
        'client_id' => $other->id,
        'date' => '2026-06-19',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(CreateGuestReservation::class)
        ->fillForm([
            'client_id' => $this->client->id,
            'date' => '2026-06-19',
            'menu1_count' => 2,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('only offers clients who eat at the cafeteria', function (): void {
    $homeDelivered = makeClient('ADOMICILE', useCafeteria: false);

    $options = livewire(CreateGuestReservation::class)
        ->instance()
        ->form
        ->getComponent('client_id')
        ->getOptions();

    expect($options)->toHaveKey($this->client->id)
        ->and($options)->not->toHaveKey($homeDelivered->id);
});

it('lets a reservation keep its own date when edited', function (): void {
    $reservation = GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-19',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(EditGuestReservation::class, ['record' => $reservation->id])
        ->fillForm([
            'menu1_count' => 4,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(GuestReservation::class, [
        'id' => $reservation->id,
        'menu1_count' => 4,
    ]);
});
