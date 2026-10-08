<?php

declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Users\Pages\ListUsers;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('security-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('runs the LDAP user sync command from the users list', function (): void {
    Artisan::shouldReceive('call')->once()->with('intranet:sync-users')->andReturn(0);

    livewire(ListUsers::class)
        ->callAction('syncUsers')
        ->assertNotified('Agents synchronisés');
});

it('reports a failing sync instead of crashing the page', function (): void {
    Artisan::shouldReceive('call')->once()->andThrow(new RuntimeException('LDAP injoignable'));

    livewire(ListUsers::class)
        ->callAction('syncUsers')
        ->assertNotified('LDAP injoignable');
});
