<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Weeks\Pages\CreateWeek;
use AcMarche\MealDelivery\Models\Week;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('fills the five week days when creating a week', function (): void {
    livewire(CreateWeek::class)
        ->fillForm(['first_day' => '2026-06-17'])
        ->call('create')
        ->assertHasNoFormErrors();

    $week = Week::query()->latest('id')->firstOrFail();

    expect($week->days)->toBe([
        '2026-06-15',
        '2026-06-16',
        '2026-06-17',
        '2026-06-18',
        '2026-06-19',
    ]);
});
