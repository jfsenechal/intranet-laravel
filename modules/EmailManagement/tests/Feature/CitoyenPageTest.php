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
        ->assertSee('Dans le dossier /var/www/gestmail, quelques commandes')
        ->assertSee('https://citoyen.marche.be/gestmail')
        ->assertSee('php artisan citoyen:update-last-login')
        ->assertSee('php artisan citoyen:orphan-mailboxes')
        ->assertSee('php artisan citoyen:orphan-mailboxes --delete')
        ->assertSee('php artisan citoyen:unread-mail --only-with-mail --min-days=90')
        ->assertSee('php artisan citoyen:unread-mail --only-with-mail --min-days=365 --delete')
        ->assertSee('Webmail citoyen');
});
