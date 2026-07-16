<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\CitoyenPage;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);
});

it('renders the citoyen page with the gestmail link and the useful commands', function (): void {
    livewire(CitoyenPage::class)
        ->assertSuccessful()
        ->assertSee('Tout se gère depuis le serveur citoyen.')
        ->assertSee('https://citoyen.marche.be/gestmail')
        ->assertSee('php artisan citoyen:purge')
        ->assertSee('php artisan citoyen:change-password')
        ->assertSee('php artisan citoyen:send-message')
        ->assertSee('Webmail citoyen');
});
