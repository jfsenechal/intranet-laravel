<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Note;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

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

it('creates a note for the client without touching timestamp columns', function (): void {
    livewire(ViewClient::class, ['record' => $this->client->id])
        ->callAction('addNote', [
            'client_id' => $this->client->id,
            'note_date' => '2026-07-13',
            'description' => 'annulation repas de la semaine',
            'is_done' => false,
        ])
        ->assertHasNoActionErrors();

    $note = Note::query()->where('client_id', $this->client->id)->sole();

    expect($note->description)->toBe('annulation repas de la semaine')
        ->and($note->is_done)->toBeFalse()
        ->and($note->note_date->format('Y-m-d'))->toBe('2026-07-13');
});
