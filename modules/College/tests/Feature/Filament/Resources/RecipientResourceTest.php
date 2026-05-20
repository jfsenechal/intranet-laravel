<?php

declare(strict_types=1);

use AcMarche\College\Enums\RolesEnum;
use AcMarche\College\Filament\Resources\Destinataires\Pages\CreateDestinataire;
use AcMarche\College\Filament\Resources\Destinataires\Pages\EditDestinataire;
use AcMarche\College\Filament\Resources\Destinataires\Pages\ListDestinataires;
use AcMarche\College\Filament\Resources\Destinataires\Pages\ViewDestinataire;
use AcMarche\College\Models\Recipient;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('college-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->convocation = User::factory()->create();
    $convocationRole = Role::factory()->create(['name' => RolesEnum::ROLE_COLLEGE_CONVOCATION->value]);
    $this->convocation->roles()->attach($convocationRole);

    $this->actingAs($this->admin);
});

it('renders list, create, view and edit pages', function (): void {
    $destinataire = Recipient::factory()->create();

    livewire(ListDestinataires::class)->assertOk();
    livewire(CreateDestinataire::class)->assertOk();
    livewire(ViewDestinataire::class, ['record' => $destinataire->id])->assertOk();
    livewire(EditDestinataire::class, ['record' => $destinataire->id])->assertOk();
});

it('lists destinataires', function (): void {
    $destinataires = Recipient::factory(3)->create();

    livewire(ListDestinataires::class)
        ->loadTable()
        ->assertCanSeeTableRecords($destinataires);
});

it('creates a destinataire via the form', function (): void {
    livewire(CreateDestinataire::class)
        ->fillForm([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'pv_college' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Recipient::class, [
        'nom' => 'Dupont',
        'prenom' => 'Jean',
        'email' => 'jean.dupont@example.com',
        'pv_college' => true,
    ]);
});

it('auto-generates the slugname if left empty', function (): void {
    livewire(CreateDestinataire::class)
        ->fillForm([
            'nom' => 'Martin',
            'prenom' => 'Marie',
            'email' => 'marie.martin@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Recipient::class, [
        'nom' => 'Martin',
        'slugname' => 'martin_marie',
    ]);
});

it('updates a destinataire via the form', function (): void {
    $destinataire = Recipient::factory()->create(['pv_college' => false]);

    livewire(EditDestinataire::class, ['record' => $destinataire->id])
        ->fillForm(['pv_college' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Recipient::class, [
        'id' => $destinataire->id,
        'pv_college' => true,
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateDestinataire::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`nom` required' => [['nom' => null, 'prenom' => 'X', 'email' => 'x@x.be'], ['nom' => 'required']],
    '`prenom` required' => [['nom' => 'X', 'prenom' => null, 'email' => 'x@x.be'], ['prenom' => 'required']],
    '`email` required' => [['nom' => 'X', 'prenom' => 'Y', 'email' => null], ['email' => 'required']],
    '`email` must be valid' => [['nom' => 'X', 'prenom' => 'Y', 'email' => 'not-an-email'], ['email' => 'email']],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListDestinataires::class)->assertForbidden();
});
