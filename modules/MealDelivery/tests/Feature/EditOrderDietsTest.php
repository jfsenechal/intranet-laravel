<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Orders\Pages\EditOrder;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
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

    $this->saltFree = Diet::create(['name' => 'Sans sel']);
    $this->sugarFree = Diet::create(['name' => 'Sans sucre']);
    $this->client->diets()->attach([$this->saltFree->id, $this->sugarFree->id]);

    $this->order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $this->client->id,
    ]);

    $meal = Meal::create([
        'order_id' => $this->order->id,
        'date' => '2026-06-15',
        'soup_count' => 0,
        'at_cafeteria' => false,
    ]);

    $this->menu1 = Menu::create(['meal_id' => $meal->id, 'position' => 1, 'quantity' => 1]);
    $this->menu2 = Menu::create(['meal_id' => $meal->id, 'position' => 2, 'quantity' => 0]);
    $this->menu1->diets()->attach($this->saltFree->id);
});

it('fills the meal diet selects from the diets already attached to each menu', function (): void {
    livewire(EditOrder::class, ['record' => $this->order->id])
        ->assertFormSet(fn (array $state): bool => $state['meals'][array_key_first($state['meals'])]['menu_1_diets'] === [$this->saltFree->id]
            && $state['meals'][array_key_first($state['meals'])]['menu_2_diets'] === []);
});

it('syncs the meal diet selects to the menus on save', function (): void {
    livewire(EditOrder::class, ['record' => $this->order->id])
        ->fillForm([
            'meals' => [[
                'date' => '2026-06-15',
                'soup_count' => 0,
                'menu_1' => 1,
                'menu_1_diets' => [$this->sugarFree->id],
                'menu_2' => 1,
                'menu_2_diets' => [$this->saltFree->id],
                'at_cafeteria' => false,
                'notes' => null,
            ]],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->menu1->refresh()->diets->pluck('id')->all())
        ->toBe([$this->sugarFree->id])
        ->and($this->menu2->refresh()->diets->pluck('id')->all())
        ->toBe([$this->saltFree->id]);
});

it('keeps a menu diet the client is no longer linked to instead of rejecting the order', function (): void {
    $this->client->diets()->detach($this->saltFree->id);

    livewire(EditOrder::class, ['record' => $this->order->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->menu1->refresh()->diets->pluck('id')->all())
        ->toBe([$this->saltFree->id]);
});

it('still refuses a diet that is neither offered nor already on the menu', function (): void {
    $foreign = Diet::create(['name' => 'Régime de quelqu\'un d\'autre']);

    livewire(EditOrder::class, ['record' => $this->order->id])
        ->fillForm([
            'meals' => [[
                'date' => '2026-06-15',
                'soup_count' => 0,
                'menu_1' => 1,
                'menu_1_diets' => [$foreign->id],
                'menu_2' => 0,
                'menu_2_diets' => [],
                'at_cafeteria' => false,
                'notes' => null,
            ]],
        ])
        ->call('save')
        ->assertHasFormErrors();
});
