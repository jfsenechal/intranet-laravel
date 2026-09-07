<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Pages\GuestsByMonth;
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
    ]);
});

it('lists the guest meals of the month for billing', function (): void {
    livewire(GuestsByMonth::class, ['month' => 6, 'year' => 2026])
        ->assertOk()
        ->assertSee('DOLCETTE')
        ->assertSee('112');
});

it('reports an empty month without failing', function (): void {
    livewire(GuestsByMonth::class, ['month' => 7, 'year' => 2026])
        ->assertOk()
        ->assertSee('Aucun repas invité pour cette période.');
});

it('totals the guest meals per resident on the pdf', function (): void {
    $summary = livewire(GuestsByMonth::class, ['month' => 6, 'year' => 2026])
        ->instance()
        ->getSummary();

    $html = view('meal-delivery::filament.pages.guests-by-month-pdf', [
        'summary' => $summary,
        'period' => 'Juin 2026',
    ])->render();

    expect($html)->toContain('DOLCETTE')
        ->toContain('Repas invités par mois');

    expect($summary['totals'])->toBe(['menu1' => 2, 'menu2' => 1, 'guests' => 3]);
});
