<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Livewire\GlobalSearch;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin-panel'));
});

it('can global search', function (): void {
    livewire(GlobalSearch::class)
        ->set('search', 'test')
        ->assertOk();
});
