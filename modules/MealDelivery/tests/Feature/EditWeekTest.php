<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\EditWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\ViewWeek;
use AcMarche\MealDelivery\Filament\Resources\Weeks\RelationManagers\OrdersRelationManager;
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
});

it('does not display the relation managers on the edit page', function (): void {
    livewire(EditWeek::class, ['record' => $this->week->id])
        ->assertOk()
        ->assertDontSee(OrdersRelationManager::class);

    expect(livewire(EditWeek::class, ['record' => $this->week->id])->instance()->getCachedRelationManagers())
        ->toBe([]);
});

it('still displays the orders relation manager on the view page', function (): void {
    livewire(ViewWeek::class, ['record' => $this->week->id])
        ->assertOk()
        ->assertSee(OrdersRelationManager::class);
});

it('saves the week from the edit page', function (): void {
    livewire(EditWeek::class, ['record' => $this->week->id])
        ->fillForm(['notes' => 'Semaine test'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->week->refresh()->notes)->toBe('Semaine test');
});
