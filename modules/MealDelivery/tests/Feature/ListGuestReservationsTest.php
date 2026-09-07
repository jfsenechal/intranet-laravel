<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\GuestReservations\Pages\ListGuestReservations;
use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\ViewResident;
use AcMarche\MealDelivery\Filament\Resources\Residents\RelationManagers\GuestReservationsRelationManager;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->resident = Resident::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'room' => '112',
        'is_active' => true,
    ]);

    $this->first = GuestReservation::create([
        'resident_id' => $this->resident->id,
        'date' => '2026-06-19',
        'menu1_count' => 2,
        'menu2_count' => 1,
    ]);

    $this->second = GuestReservation::create([
        'resident_id' => $this->resident->id,
        'date' => '2026-06-20',
        'menu1_count' => 1,
        'menu2_count' => 3,
    ]);
});

it('lists the guest reservations', function (): void {
    livewire(ListGuestReservations::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$this->first, $this->second]);
});

it('sums the expected head count across the table', function (): void {
    livewire(ListGuestReservations::class)
        ->loadTable()
        ->assertTableColumnSummarySet('menu1_count', 'sum', 3)
        ->assertTableColumnSummarySet('menu2_count', 'sum', 4)
        ->assertTableColumnSummarySet('total', 'sum', 7);
});

it('lists a resident own guest reservations under their page', function (): void {
    livewire(GuestReservationsRelationManager::class, [
        'ownerRecord' => $this->resident,
        'pageClass' => ViewResident::class,
    ])
        ->loadTable()
        ->assertCanSeeTableRecords([$this->first, $this->second]);
});

it('books a guest meal straight from the resident page', function (): void {
    livewire(GuestReservationsRelationManager::class, [
        'ownerRecord' => $this->resident,
        'pageClass' => ViewResident::class,
    ])
        ->loadTable()
        ->assertActionVisible(TestAction::make('create')->table())
        ->callAction(TestAction::make('create')->table(), [
            'date' => '2026-06-21',
            'menu1_count' => 3,
            'menu2_count' => 0,
        ])
        ->assertHasNoActionErrors();

    $booked = GuestReservation::query()
        ->where('resident_id', $this->resident->id)
        ->whereDate('date', '2026-06-21')
        ->sole();

    expect($booked->menu1_count)->toBe(3)
        ->and($booked->menu2_count)->toBe(0);
});

it('still refuses a duplicate date when booking from the resident page', function (): void {
    livewire(GuestReservationsRelationManager::class, [
        'ownerRecord' => $this->resident,
        'pageClass' => ViewResident::class,
    ])
        ->loadTable()
        ->callAction(TestAction::make('create')->table(), [
            'date' => '2026-06-19',
            'menu1_count' => 1,
            'menu2_count' => 0,
        ])
        ->assertHasActionErrors(['date']);

    expect(GuestReservation::query()->where('resident_id', $this->resident->id)->count())->toBe(2);
});
