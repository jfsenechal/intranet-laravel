<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\CiviliteEnum;
use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\CreateMembre;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\EditMembre;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\ListMembres;
use AcMarche\ActivityManager\Filament\Resources\Membres\Pages\ViewMembre;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('activity-manager-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->mdaAdmin = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin->roles()->attach($role);

    $this->actingAs($this->admin);
});

it('renders list, create, view and edit pages', function (): void {
    $membre = Member::factory()->create();

    livewire(ListMembres::class)->assertOk();
    livewire(CreateMembre::class)->assertOk();
    livewire(ViewMembre::class, ['record' => $membre->id])->assertOk();
    livewire(EditMembre::class, ['record' => $membre->id])->assertOk();
});

it('lists membres', function (): void {
    $membres = Member::factory(3)->create();

    livewire(ListMembres::class)
        ->loadTable()
        ->assertCanSeeTableRecords($membres);
});

it('creates a membre via the form', function (): void {
    livewire(CreateMembre::class)
        ->fillForm([
            'civilite' => CiviliteEnum::MADAME->value,
            'nom' => 'Dupont',
            'prenom' => 'Marie',
            'email' => 'marie.dupont@example.com',
            'enabled' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Member::class, [
        'nom' => 'Dupont',
        'prenom' => 'Marie',
        'email' => 'marie.dupont@example.com',
        'civilite' => 'Madame',
    ]);
});

it('updates a membre via the form', function (): void {
    $membre = Member::factory()->create(['enabled' => true]);

    livewire(EditMembre::class, ['record' => $membre->id])
        ->fillForm(['enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Member::class, [
        'id' => $membre->id,
        'enabled' => false,
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateMembre::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`nom` required' => [['nom' => null, 'prenom' => 'X'], ['nom' => 'required']],
    '`prenom` required' => [['nom' => 'X', 'prenom' => null], ['prenom' => 'required']],
    '`email` invalid' => [['nom' => 'X', 'prenom' => 'Y', 'email' => 'not-an-email'], ['email' => 'email']],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListMembres::class)->assertForbidden();
});
