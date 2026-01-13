<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Security\Models\Role;
use App\Models\User;
use DirectoryTree\ImapEngine\Laravel\ImapManager;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use Filament\Facades\Filament;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

describe('Inbox Page Access', function () {
    test('admin user can access inbox page', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(Inbox::getUrl())
            ->assertSuccessful();
    });

    test('user with ROLE_INDICATEUR_VILLE_ADMIN can access inbox page', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->addRole($role);

        $this->actingAs($user)
            ->get(Inbox::getUrl())
            ->assertSuccessful();
    });

    test('regular user cannot access inbox page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(Inbox::getUrl())
            ->assertForbidden();
    });

    test('guest cannot access inbox page', function () {
        $this->get(Inbox::getUrl())
            ->assertRedirect();
    });
});

describe('Inbox Page Display', function () {
    test('inbox page displays correct title', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(Inbox::getUrl())
            ->assertSee('Boite mail');
    });

    test('inbox page handles IMAP connection errors gracefully', function () {
        // Swap the mailbox with a fake that will return empty
        $fakeMailbox = new FakeMailbox;
        $manager = app(ImapManager::class);
        $manager->swap('indicateur_ville', $fakeMailbox);

        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(Inbox::getUrl())
            ->assertSuccessful();
    });
});

describe('Inbox Page canAccess method', function () {
    test('canAccess returns true for administrator', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin);

        expect(Inbox::canAccess())->toBeTrue();
    });

    test('canAccess returns true for user with correct role', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->addRole($role);

        $this->actingAs($user);

        expect(Inbox::canAccess())->toBeTrue();
    });

    test('canAccess returns false for user without correct role', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        expect(Inbox::canAccess())->toBeFalse();
    });
});
