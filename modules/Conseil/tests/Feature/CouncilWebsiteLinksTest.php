<?php

declare(strict_types=1);

use AcMarche\Conseil\Filament\Pages\CouncilWebsiteLinks;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('conseil-panel'));

    $this->actingAs(User::factory()->create());
});

it('renders the council website links page', function (): void {
    livewire(CouncilWebsiteLinks::class)
        ->assertOk()
        ->assertSee('Le Conseil sur le site www.marche.be')
        ->assertSee("Consulter l'ordre du Conseil")
        ->assertSee('Consulter la liste des PV')
        ->assertSee('Consulter les membres du Conseil')
        ->assertSeeHtml('https://www.marche.be/administration/le-conseil-communal/ordre-du-jour-du-conseil-1650/');
});
