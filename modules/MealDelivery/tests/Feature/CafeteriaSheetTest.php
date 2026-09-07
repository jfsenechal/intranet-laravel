<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\CafeteriaSheet;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\RouteSheets;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Resident;
use AcMarche\MealDelivery\Models\Week;
use AcMarche\MealDelivery\Service\RouteSheetsAggregator;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->week = Week::create([
        'first_day' => '2026-06-15',
        'days' => ['2026-06-15'],
    ]);

    $this->cafeteriaClient = Client::create([
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

    $order = Order::create([
        'week_id' => $this->week->id,
        'client_id' => $this->cafeteriaClient->id,
    ]);

    $meal = Meal::create([
        'date' => '2026-06-15',
        'soup_count' => 1,
        'order_id' => $order->id,
        'at_cafeteria' => true,
    ]);

    Menu::create([
        'position' => 1,
        'quantity' => 1,
        'meal_id' => $meal->id,
    ]);
});

it('renders the cafeteria sheet for a given day with its clients', function (): void {
    livewire(CafeteriaSheet::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertSee('BOON Micheline')
        ->assertSee('Cafétariat');
});

it('explains the RF and DF columns with a legend', function (): void {
    livewire(CafeteriaSheet::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertSee('reprendre la feuille')
        ->assertSee('donner une nouvelle feuille');
});

it('explains the RF and DF columns with a legend on the pdf sheet', function (): void {
    $sheet = (new RouteSheetsAggregator())->build($this->week, '2026-06-15')['cafeteria'];

    $html = view('meal-delivery::filament.resources.weeks.pages.route-sheet-pdf', [
        'date' => CarbonImmutable::parse('2026-06-15'),
        'sheet' => $sheet,
        'heading' => 'Cafétariat',
    ])->render();

    expect($html)->toContain('reprendre la feuille')
        ->toContain('donner une nouvelle feuille');
});

it('never shows guest meals on the cafeteria sheet', function (): void {
    $resident = Resident::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'is_active' => true,
    ]);

    GuestReservation::create([
        'resident_id' => $resident->id,
        'date' => '2026-06-15',
        'menu1_count' => 3,
        'menu2_count' => 2,
    ]);

    livewire(CafeteriaSheet::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertDontSee('Repas invités')
        ->assertDontSee('DOLCETTE');
});

it('never shows guest meals on the delivery route sheets', function (): void {
    $resident = Resident::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'is_active' => true,
    ]);

    GuestReservation::create([
        'resident_id' => $resident->id,
        'date' => '2026-06-15',
        'menu1_count' => 3,
        'menu2_count' => 2,
    ]);

    livewire(RouteSheets::class, [
        'record' => $this->week,
        'date' => '2026-06-15',
    ])
        ->assertOk()
        ->assertDontSee('Repas invités')
        ->assertDontSee('DOLCETTE');
});
