<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

/**
 * The collapsed desktop sidebar is only as wide as a nav icon, so
 * `resources/css/filament/admin/theme.css` shrinks these shortcuts to an icon
 * square and hides their labels. Both rules key off the markup asserted here.
 */
it('renders the home shortcut with a hideable label', function (): void {
    $html = Blade::render('<x-home-button/>');

    expect($html)
        ->toContain('fi-sidebar-shortcut')
        ->toContain('title="Accueil"');

    expect($html)->toMatch('/<span class="fi-sidebar-shortcut-label">\s*Accueil\s*<\/span>/');
});

it('renders the applications shortcut with a hideable label', function (): void {
    $this->actingAs(User::factory()->create());

    $html = Livewire::test('modules-launcher')->html();

    expect($html)
        ->toContain('fi-sidebar-shortcut')
        ->toContain('title="Applications"');

    expect($html)->toMatch('/<span class="fi-sidebar-shortcut-label">\s*Applications\s*<\/span>/');
});
