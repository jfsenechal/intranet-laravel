<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\Pages\ViewClient;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\CreateOrder;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    // Freeze to a Tuesday so "start of current week" is Monday 2026-07-13.
    CarbonImmutable::setTestNow('2026-07-14');

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

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

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('redirects to the create order page for the chosen week', function (): void {
    $week = Week::create(['first_day' => '2026-07-20', 'days' => ['2026-07-20']]);

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->callAction('addOrder', ['week_id' => $week->id])
        ->assertHasNoActionErrors()
        ->assertRedirect(CreateOrder::getUrl([
            'week_id' => $week->id,
            'client_id' => $this->client->id,
        ]));
});

it('hides the add order action when only past weeks exist', function (): void {
    Week::create(['first_day' => '2026-07-06', 'days' => ['2026-07-06']]);

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertActionHidden('addOrder');
});

it('does not offer weeks the client already has an order for', function (): void {
    $orderedWeek = Week::create(['first_day' => '2026-07-20', 'days' => ['2026-07-20']]);
    Order::create(['week_id' => $orderedWeek->id, 'client_id' => $this->client->id]);

    // The only upcoming week is already ordered, so nothing is left to offer.
    livewire(ViewClient::class, ['record' => $this->client->id])
        ->assertActionHidden('addOrder');
});

it('offers at most five weeks starting from the current week', function (): void {
    // Six consecutive upcoming weeks; only the first five may be selected.
    $weeks = collect(range(0, 5))
        ->map(fn (int $offset): Week => Week::create([
            'first_day' => CarbonImmutable::parse('2026-07-13')->addWeeks($offset)->format('Y-m-d'),
        ]));

    $sixthWeek = $weeks->last();

    livewire(ViewClient::class, ['record' => $this->client->id])
        ->callAction('addOrder', ['week_id' => $sixthWeek->id])
        ->assertHasActionErrors(['week_id']);
});
