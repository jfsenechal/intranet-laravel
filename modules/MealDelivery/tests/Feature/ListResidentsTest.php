<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Residents\Pages\ListResidents;
use AcMarche\MealDelivery\Models\Resident;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->active = Resident::create([
        'last_name' => 'DOLCETTE',
        'first_name' => 'Marcel',
        'room' => '112',
        'is_active' => true,
    ]);

    $this->former = Resident::create([
        'last_name' => 'PARTI',
        'first_name' => 'Jeanne',
        'room' => '204',
        'is_active' => false,
    ]);
});

it('lists the active residents by default', function (): void {
    livewire(ListResidents::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$this->active])
        ->assertCanNotSeeTableRecords([$this->former]);
});

it('prints the active residents on the directory pdf', function (): void {
    $html = view('meal-delivery::filament.resources.residents.pages.residents-pdf', [
        'residents' => Resident::query()->where('is_active', true)->orderBy('last_name')->get(),
        'includeInactive' => false,
        'printedAt' => CarbonImmutable::parse('2026-09-07'),
    ])->render();

    expect($html)->toContain('LISTE DES RÉSIDENTS')
        ->toContain('DOLCETTE')
        ->toContain('112')
        ->not->toContain('PARTI');
});

it('adds the former residents and an active column when asked for them', function (): void {
    $html = view('meal-delivery::filament.resources.residents.pages.residents-pdf', [
        'residents' => Resident::query()->orderBy('last_name')->get(),
        'includeInactive' => true,
        'printedAt' => CarbonImmutable::parse('2026-09-07'),
    ])->render();

    expect($html)->toContain('DOLCETTE')
        ->toContain('PARTI')
        ->toContain('Actif')
        ->toContain('Tous les résidents');
});
