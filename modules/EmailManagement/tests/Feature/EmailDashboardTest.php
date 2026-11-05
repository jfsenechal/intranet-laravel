<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Filament\Pages\EmailDashboard;
use AcMarche\EmailManagement\Filament\Resources\Citoyens\Widgets\ExpiredAccountsWidget;
use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('email-management-panel'));

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    $this->actingAs($user);
});

it('opens as the home of the panel with the expired accounts widget', function (): void {
    $this->get(EmailDashboard::getUrl())
        ->assertOk()
        ->assertSeeLivewire(ExpiredAccountsWidget::class);
});

it('is denied to a user without ROLE_EMAIL_ADMIN', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    livewire(EmailDashboard::class)->assertForbidden();
});

it('lists the accounts unused for two years or never used', function (): void {
    $stale = Citoyen::factory()->create(['last_connection' => now()->subYears(3)]);
    $never = Citoyen::factory()->create(['last_connection' => null]);
    $recent = Citoyen::factory()->create(['last_connection' => now()->subMonth()]);

    livewire(ExpiredAccountsWidget::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$stale, $never])
        ->assertCanNotSeeTableRecords([$recent])
        ->assertSee('2 comptes à supprimer');
});

it('deletes an expired account and its login from the widget', function (): void {
    $expired = Citoyen::factory()->create(['last_connection' => now()->subYears(3)]);
    $other = Citoyen::factory()->create(['last_connection' => null]);

    livewire(ExpiredAccountsWidget::class)
        ->loadTable()
        ->callAction(TestAction::make('delete')->table($expired))
        ->assertNotified()
        ->assertCanNotSeeTableRecords([$expired])
        ->assertCanSeeTableRecords([$other]);

    expect(Citoyen::query()->find($expired->id))->toBeNull()
        ->and(VirtualUser::query()->where('username', $expired->mail)->exists())->toBeFalse()
        ->and(VirtualUser::query()->where('username', $other->mail)->exists())->toBeTrue();
});

it('hides the delete action from a user without ROLE_EMAIL_ADMIN', function (): void {
    $expired = Citoyen::factory()->create(['last_connection' => null]);

    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    livewire(ExpiredAccountsWidget::class)
        ->loadTable()
        ->assertActionHidden(TestAction::make('delete')->table($expired));
});
