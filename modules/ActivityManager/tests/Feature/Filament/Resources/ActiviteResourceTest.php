<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\CreateActivite;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\EditActivite;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\ListActivites;
use AcMarche\ActivityManager\Filament\Resources\Activites\Pages\ViewActivite;
use AcMarche\ActivityManager\Models\Activite;
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
    $activite = Activite::factory()->create();

    livewire(ListActivites::class)->assertOk();
    livewire(CreateActivite::class)->assertOk();
    livewire(ViewActivite::class, ['record' => $activite->id])->assertOk();
    livewire(EditActivite::class, ['record' => $activite->id])->assertOk();
});

it('lists activites', function (): void {
    $activites = Activite::factory(3)->create();

    livewire(ListActivites::class)
        ->loadTable()
        ->assertCanSeeTableRecords($activites);
});

it('creates an activite via the form', function (): void {
    livewire(CreateActivite::class)
        ->fillForm([
            'nom' => 'Yoga',
            'description' => 'Cours de yoga doux',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Activite::class, [
        'nom' => 'Yoga',
        'description' => 'Cours de yoga doux',
    ]);
});

it('updates an activite via the form', function (): void {
    $activite = Activite::factory()->create(['nom' => 'Tricot']);

    livewire(EditActivite::class, ['record' => $activite->id])
        ->fillForm(['nom' => 'Tricot Avancé'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Activite::class, [
        'id' => $activite->id,
        'nom' => 'Tricot Avancé',
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateActivite::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`nom` required' => [['nom' => null], ['nom' => 'required']],
    '`nom` max 150' => [['nom' => str_repeat('a', 151)], ['nom' => 'max']],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListActivites::class)->assertForbidden();
});
