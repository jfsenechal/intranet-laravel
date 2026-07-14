<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\ViewOrder;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15'],
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

    $this->order = Order::create([
        'week_id' => $week->id,
        'client_id' => $this->client->id,
    ]);

    $this->meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 2,
        'order_id' => $this->order->id,
        'at_cafeteria' => false,
    ]);

    $this->menu = Menu::create([
        'position' => 1,
        'quantity' => 3,
        'meal_id' => $this->meal->id,
    ]);

    $this->diet = Diet::create(['name' => 'Sans sel']);
    $this->menu->diets()->attach($this->diet->id);
});

it('cascades deletion to meals, menus and diet links when an order is deleted', function (): void {
    livewire(ViewOrder::class, ['record' => $this->order->id])
        ->callAction(DeleteAction::class)
        ->assertHasNoActionErrors()
        ->assertRedirect(ClientResource::getUrl('view', ['record' => $this->client->id]));

    expect(Order::query()->whereKey($this->order->id)->exists())->toBeFalse()
        ->and(Meal::query()->whereKey($this->meal->id)->exists())->toBeFalse()
        ->and(Menu::query()->whereKey($this->menu->id)->exists())->toBeFalse()
        ->and(DB::connection('maria-meal-delivery')->table('diet_menu')->where('menu_id', $this->menu->id)->exists())->toBeFalse();

    expect(Diet::query()->whereKey($this->diet->id)->exists())->toBeTrue();
});
