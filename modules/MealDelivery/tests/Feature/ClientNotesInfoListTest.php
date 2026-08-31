<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use AcMarche\MealDelivery\Filament\Resources\Notes\NoteResource;
use AcMarche\MealDelivery\Filament\Resources\Notes\Pages\ViewNote;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true, 'username' => 'jdupont']));

    $this->client = Client::create([
        'last_name' => 'Dupont',
        'first_name' => fake()->firstName(),
        'street' => fake()->streetName(),
        'number' => (string) fake()->buildingNumber(),
        'postal_code' => 6900,
        'city' => fake()->city(),
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'notes' => 'Remarque du client',
    ]);
});

it('lists the notes of the client on the view page', function (): void {
    $this->client->notes()->create([
        'note_date' => '2026-07-13',
        'description' => 'annulation repas de la semaine',
    ]);

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertOk()
        ->assertSee('annulation repas de la semaine')
        ->assertSee('13/07/2026')
        ->assertSee('jdupont')
        ->assertSee('Remarque du client');
});

it('does not list the notes of another client', function (): void {
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
    $otherClient->notes()->create(['description' => 'note du voisin']);

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertOk()
        ->assertDontSee('note du voisin');
});

it('shows a placeholder when the client has no note', function (): void {
    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertOk()
        ->assertSee('Aucune note');
});

it('links each listed note to its view page', function (): void {
    $note = $this->client->notes()->create([
        'note_date' => '2026-07-13',
        'description' => 'annulation repas de la semaine',
    ]);

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertOk()
        ->assertSee(NoteResource::getUrl('view', ['record' => $note->id]));
});

it('displays the note on its own view page', function (): void {
    $note = $this->client->notes()->create([
        'note_date' => '2026-07-13',
        'description' => 'annulation repas de la semaine',
    ]);

    livewire(ViewNote::class, ['record' => $note->id])
        ->assertOk()
        ->assertSee('Note du 13/07/2026')
        ->assertSee('annulation repas de la semaine')
        ->assertSee('jdupont')
        ->assertSee('Dupont');
});
