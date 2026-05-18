<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\CreateCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\EditCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\ListCours;
use AcMarche\ActivityManager\Filament\Resources\Cours\Pages\ViewCours;
use AcMarche\ActivityManager\Models\Activite;
use AcMarche\ActivityManager\Models\Cours;
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
    $cours = Cours::factory()->create();

    livewire(ListCours::class)->assertOk();
    livewire(CreateCours::class)->assertOk();
    livewire(ViewCours::class, ['record' => $cours->id])->assertOk();
    livewire(EditCours::class, ['record' => $cours->id])->assertOk();
});

it('lists cours', function (): void {
    $cours = Cours::factory(3)->create();

    livewire(ListCours::class)
        ->loadTable()
        ->assertCanSeeTableRecords($cours);
});

it('creates a cours via the form', function (): void {
    $activite = Activite::factory()->create();

    livewire(CreateCours::class)
        ->fillForm([
            'nom' => 'Yoga - Septembre 2026',
            'date_debut' => '2026-09-01',
            'date_fin' => '2026-12-31',
            'activite_id' => $activite->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Cours::class, [
        'nom' => 'Yoga - Septembre 2026',
        'activite_id' => $activite->id,
    ]);
});

it('updates a cours via the form', function (): void {
    $cours = Cours::factory()->create(['nom' => 'Old']);

    livewire(EditCours::class, ['record' => $cours->id])
        ->fillForm(['nom' => 'New'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Cours::class, [
        'id' => $cours->id,
        'nom' => 'New',
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateCours::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`nom` required' => [['nom' => null, 'date_debut' => '2026-09-01'], ['nom' => 'required']],
    '`date_debut` required' => [['nom' => 'X', 'date_debut' => null], ['date_debut' => 'required']],
    '`date_fin` before date_debut' => [
        ['nom' => 'X', 'date_debut' => '2026-09-01', 'date_fin' => '2026-08-01'],
        ['date_fin' => 'after_or_equal'],
    ],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListCours::class)->assertForbidden();
});
