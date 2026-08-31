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

    $this->user = User::factory()->create(['is_administrator' => true, 'username' => 'jdupont']);

    $this->actingAs($this->user);

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

it('stamps the note with the username of its author', function (): void {
    livewire(ViewClient::class, ['record' => $this->client->id])
        ->callAction('addNote', [
            'client_id' => $this->client->id,
            'note_date' => '2026-07-13',
            'description' => 'appel du fils',
            'is_done' => false,
        ])
        ->assertHasNoActionErrors();

    expect(Note::query()->where('client_id', $this->client->id)->sole()->user_add)
        ->toBe('jdupont');
});

it('does not let the form overwrite the author', function (): void {
    $note = $this->client->notes()->create([
        'note_date' => '2026-07-13',
        'description' => 'note initiale',
        'is_done' => false,
        'user_add' => 'someone_else',
    ]);

    expect($note->user_add)->toBe('jdupont');
});
