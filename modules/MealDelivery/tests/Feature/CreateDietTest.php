<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Resources\Diets\Pages\CreateDiet;
use AcMarche\MealDelivery\Models\Diet;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('creates a diet', function (): void {
    livewire(CreateDiet::class)
        ->fillForm([
            'name' => 'Pas de croquettes -> PDT',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Diet::class, [
        'name' => 'Pas de croquettes -> PDT',
    ]);
});
