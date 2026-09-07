<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Pages\GuestsKitchenExport;
use AcMarche\MealDelivery\Models\GuestReservation;
use AcMarche\MealDelivery\Models\Resident;
use App\Models\User;
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

    GuestReservation::create([
        'resident_id' => $this->resident->id,
        'date' => '2026-06-19',
        'menu1_count' => 2,
        'menu2_count' => 1,
        'notes' => 'Arrivée 11h30',
    ]);
});

it('lists the guest meals of the day with their totals', function (): void {
    livewire(GuestsKitchenExport::class, ['date' => '2026-06-19'])
        ->assertOk()
        ->assertSee('DOLCETTE Marcel')
        ->assertSee('112')
        ->assertSee('Arrivée 11h30');
});

it('reports a day without any guest', function (): void {
    livewire(GuestsKitchenExport::class, ['date' => '2026-06-20'])
        ->assertOk()
        ->assertSee('Aucun repas invité pour ce jour.');
});

it('falls back to today when no date is given', function (): void {
    livewire(GuestsKitchenExport::class)
        ->assertOk()
        ->assertSet('date', today()->format('Y-m-d'));
});

it('totals the guest menus on the pdf', function (): void {
    $summary = livewire(GuestsKitchenExport::class, ['date' => '2026-06-19'])
        ->instance()
        ->getSummary();

    $html = view('meal-delivery::filament.pages.guests-kitchen-export-pdf', [
        'summary' => $summary,
        'formattedDate' => 'Vendredi 19 Juin 2026',
    ])->render();

    expect($html)->toContain('REPAS INVITÉS')
        ->toContain('DOLCETTE Marcel');

    expect($summary['totals'])->toBe([
        'residents' => 1,
        'menu1' => 2,
        'menu2' => 1,
        'guests' => 3,
    ]);
});
