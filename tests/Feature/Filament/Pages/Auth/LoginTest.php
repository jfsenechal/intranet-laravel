<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

test('an unauthenticated user can access the login page', function () {
    auth()->logout();

    $this->get(Filament::getLoginUrl())
        ->assertOk();
});

test('an unauthenticated user can not access the admin panel', function () {
    auth()->logout();

    $this->get('admin')
        ->assertRedirect(Filament::getLoginUrl());
});

test('an unauthenticated user can login', function () {
    $user = User::factory()->create(['username' => 'default.user']);

    Filament::setCurrentPanel(Filament::getPanel('admin-panel'));
    auth()->logout();

    // The login form field named "email" actually holds the username, which is
    // what LdapAuthService::checkPassword() looks the account up by.
    livewire(Login::class)
        ->fillForm([
            'email' => $user->username,
            'password' => config('app.default_user.password'),
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertAuthenticatedAs($user);
});

test('an authenticated user can access the admin panel', function () {
    // The panel root redirects authenticated users to their home page via
    // RedirectToHomeController; only unauthenticated users land on the login page.
    $this->get('admin')
        ->assertRedirect()
        ->assertRedirectContains('admin');

    expect($this->get('admin')->headers->get('Location'))
        ->not->toBe(Filament::getLoginUrl());
});

test('an authenticated user can logout', function () {
    $this->assertAuthenticated();

    $this->post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
