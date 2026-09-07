<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\CreateGuestReservation;
use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\EditGuestReservation;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

function makeResident(string $lastName, bool $isActive = true): Resident
{
    return Resident::create([
        'last_name' => $lastName,
        'first_name' => fake()->firstName(),
        'room' => (string) fake()->numberBetween(100, 400),
        'is_active' => $isActive,
    ]);
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->resident = makeResident('DOLCETTE');
});

it('creates a guest reservation split over the two menus', function (): void {
    livewire(CreateGuestReservation::class)
        ->fillForm([
            'resident_id' => $this->resident->id,
            'date' => '2026-06-19',
            'menu1_count' => 2,
            'menu2_count' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(GuestReservation::class, [
        'resident_id' => $this->resident->id,
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);
});

it('rejects a reservation without a single meal', function (): void {
    livewire(CreateGuestReservation::class)
        ->fillForm([
            'resident_id' => $this->resident->id,
            'date' => '2026-06-19',
            'menu1_count' => 0,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['menu1_count']);
});

it('rejects a second reservation for the same resident on the same day', function (): void {
    GuestReservation::create([
        'resident_id' => $this->resident->id,
        'date' => '2026-06-19',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(CreateGuestReservation::class)
        ->fillForm([
            'resident_id' => $this->resident->id,
            'date' => '2026-06-19',
            'menu1_count' => 3,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasFormErrors(['date']);
});

it('allows the same day for two different residents', function (): void {
    $other = makeResident('HERGOT');

    GuestReservation::create([
        'resident_id' => $other->id,
        'date' => '2026-06-19',
        'menu1_count' => 1,
        'menu2_count' => 0,
    ]);

    livewire(CreateGuestReservation::class)
        ->fillForm([
            'resident_id' => $this->resident->id,
            'date' => '2026-06-19',
            'menu1_count' => 2,
            'menu2_count' => 0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('only offers residents who are still active', function (): void {
    $formerResident = makeResident('PARTI', isActive: false);

    $options = livewire(CreateGuestReservation::class)
        ->instance()
        ->form
        ->getComponent('resident_id')
        ->getOptions();

    expect($options)->toHaveKey($this->resident->id)
        ->and($options)->not->toHaveKey($formerResident->id);
});

it('lets a reservation keep its own date when edited', function (): void {
    $reservation = GuestReservation::create([
        'resident_id' => $this->resident->id,
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
