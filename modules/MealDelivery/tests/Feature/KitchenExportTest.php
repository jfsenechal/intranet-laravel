<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\KitchenExport;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\KitchenExportAggregator;
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
        'last_name' => 'BOON',
        'first_name' => 'Micheline',
        'street' => 'Chaussée de Liège',
        'number' => '39/32',
        'postal_code' => 6900,
        'city' => 'MARCHE',
        'route_id' => DeliveryRoute::create(['name' => fake()->unique()->word()])->id,
        'is_active' => true,
        'use_cafeteria' => true,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 1,
        'order_id' => Order::create([
            'week_id' => $this->week->id,
            'client_id' => $this->client->id,
        ])->id,
        'at_cafeteria' => false,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => 4,
        'meal_id' => $meal->id,
    ]);
});

it('adds the guest menus to the kitchen total and details them in their own table', function (): void {
    GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-15',
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);

    livewire(KitchenExport::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertSee('Repas invités')
        ->assertSee('aucun régime pour ces menus')
        ->assertSee('dont 3 invités')
        ->assertSeeHtml('<strong>Menus :</strong> 7');
});

it('omits the guest table when nobody receives family', function (): void {
    livewire(KitchenExport::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertDontSee('invité')
        ->assertSeeHtml('<strong>Menus :</strong> 4');
});

it('prints the guest table on the pdf export', function (): void {
    GuestReservation::create([
        'client_id' => $this->client->id,
        'date' => '2026-06-15',
        'menu1_count' => 0,
        'menu2_count' => 5,
    ]);

    $html = view('meal-delivery::filament.resources.weeks.pages.kitchen-export-pdf', [
        'summary' => (new KitchenExportAggregator())->build($this->week, '2026-06-15'),
    ])->render();

    expect($html)->toContain('Repas invités')
        ->toContain('dont 5 invités');
});
