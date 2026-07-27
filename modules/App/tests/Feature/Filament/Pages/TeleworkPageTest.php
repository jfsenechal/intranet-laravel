<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\TeleworkPage;
use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Models\Telework;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    $this->user = User::factory()->create(['username' => 'mmartin']);
    $this->actingAs($this->user);
});

it('renders the telework page', function (): void {
    Livewire::test(TeleworkPage::class)->assertOk();
});

it('creates a telework request with the address on save', function (): void {
    Livewire::test(TeleworkPage::class)
        ->fillForm([
            'street' => 'Rue de la Belle Eau 37',
            'postal_code' => '6640',
            'locality' => 'Vaux-sur-Sûre',
            'location_type' => LocationTypeEnum::Domicile->value,
            'day_type' => DayTypeEnum::Variable->value,
            'variable_day_reason' => '<p>Jour variable en fonction des nécessités du service</p>',
            'regulation_agreement' => true,
            'it_agreement' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Telework::class, [
        'user_add' => 'mmartin',
        'street' => 'Rue de la Belle Eau 37',
        'postal_code' => '6640',
        'locality' => 'Vaux-sur-Sûre',
    ]);
});

it('updates the existing request instead of creating a second one', function (): void {
    Telework::factory()->create([
        'user_add' => 'mmartin',
        'postal_code' => '6900',
    ]);

    Livewire::test(TeleworkPage::class)
        ->fillForm(['postal_code' => '6640'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Telework::query()->where('user_add', 'mmartin')->count())->toBe(1);

    assertDatabaseHas(Telework::class, [
        'user_add' => 'mmartin',
        'postal_code' => '6640',
    ]);
});
