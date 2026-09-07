<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\CreateResident;
use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\ViewResident;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('creates a resident', function (): void {
    livewire(CreateResident::class)
        ->fillForm([
            'last_name' => 'DOLCETTE',
            'first_name' => 'Marcel',
            'room' => '112',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Resident::class, [
        'last_name' => 'DOLCETTE',
        'room' => '112',
    ]);
});

it('requires a last name', function (): void {
    livewire(CreateResident::class)
        ->fillForm([
            'last_name' => null,
            'first_name' => 'Marcel',
        ])
        ->call('create')
        ->assertHasFormErrors(['last_name' => 'required']);
});

it('cascades deletion to the guest reservations of the resident', function (): void {
    $resident = Resident::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'is_active' => true,
    ]);

    $reservation = GuestReservation::create([
        'resident_id' => $resident->id,
        'date' => '2026-06-19',
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);

    livewire(ViewResident::class, ['record' => $resident->id])
        ->callAction(DeleteAction::class)
        ->assertHasNoActionErrors();

    expect(Resident::query()->whereKey($resident->id)->exists())->toBeFalse()
        ->and(GuestReservation::query()->whereKey($reservation->id)->exists())->toBeFalse();
});
