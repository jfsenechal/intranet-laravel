<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\CreateCategorie;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\EditCategorie;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ListCategories;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ViewCategorie;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\ChildrenRelationManager;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\FichesRelationManager;
use AcMarche\CpasLibrary\Models\Categorie;
use AcMarche\CpasLibrary\Models\Fiche;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('cpas-library-panel'));

    $this->admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY_ADMIN->value]);
    $this->admin->roles()->attach($adminRole);

    $this->member = User::factory()->create();
    $memberRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]);
    $this->member->roles()->attach($memberRole);

    $this->actingAs($this->admin);
});

it('renders the list, create, view and edit pages for an admin', function (): void {
    $categorie = Categorie::factory()->create();

    livewire(ListCategories::class)->assertOk();
    livewire(CreateCategorie::class)->assertOk();
    livewire(ViewCategorie::class, ['record' => $categorie->id])->assertOk();
    livewire(EditCategorie::class, ['record' => $categorie->id])->assertOk();
});

it('lists categories', function (): void {
    $categories = Categorie::factory(3)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->assertCanSeeTableRecords($categories);
});

it('has the expected table columns', function (string $column): void {
    livewire(ListCategories::class)->assertTableColumnExists($column);
})->with(['name', 'parent.name', 'public', 'fiches_count']);

it('searches by name', function (): void {
    $categories = Categorie::factory(3)->create();
    $needle = $categories->first()->name;

    livewire(ListCategories::class)
        ->loadTable()
        ->searchTable($needle)
        ->assertCanSeeTableRecords($categories->where('name', $needle))
        ->assertCanNotSeeTableRecords($categories->where('name', '!=', $needle));
});

it('filters by parent', function (): void {
    $parent = Categorie::factory()->create();
    $children = Categorie::factory(2)->create(['parent_id' => $parent->id]);
    $orphans = Categorie::factory(2)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->filterTable('parent_id', $parent->id)
        ->assertCanSeeTableRecords($children)
        ->assertCanNotSeeTableRecords($orphans);
});

it('creates a categorie via the form', function (): void {
    livewire(CreateCategorie::class)
        ->fillForm([
            'name' => 'Aide sociale',
            'slug' => 'aide-sociale',
            'departments' => ['Cpas'],
            'public' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Categorie::class, [
        'name' => 'Aide sociale',
        'slug' => 'aide-sociale',
        'public' => true,
    ]);
});

it('updates a categorie via the form', function (): void {
    $categorie = Categorie::factory()->create(['public' => false]);

    livewire(EditCategorie::class, ['record' => $categorie->id])
        ->fillForm([
            'name' => 'Renamed',
            'public' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Categorie::class, [
        'id' => $categorie->id,
        'name' => 'Renamed',
        'public' => true,
    ]);
});

it('deletes a categorie from the view page', function (): void {
    $categorie = Categorie::factory()->create();

    livewire(ViewCategorie::class, ['record' => $categorie->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertDatabaseMissing($categorie);
});

it('validates the form data', function (array $data, array $errors): void {
    $categorie = Categorie::factory()->create();

    livewire(EditCategorie::class, ['record' => $categorie->id])
        ->fillForm([
            'name' => 'Valid name',
            'departments' => ['Cpas'],
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`departments` is required' => [['departments' => []], ['departments' => 'required']],
]);

it('excludes the current record from the parent_id options', function (): void {
    $categorie = Categorie::factory()->create();

    livewire(EditCategorie::class, ['record' => $categorie->id])
        ->fillForm(['parent_id' => $categorie->id])
        ->call('save')
        ->assertHasFormErrors(['parent_id']);
});

it('forbids a user without a library role from listing categories', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListCategories::class)->assertForbidden();
});

it('forbids a ROLE_LIBRARY user from creating a categorie', function (): void {
    $this->actingAs($this->member);

    livewire(CreateCategorie::class)->assertForbidden();
});

it('hides the create header action for a ROLE_LIBRARY user', function (): void {
    $this->actingAs($this->member);

    livewire(ListCategories::class)->assertActionHidden(CreateAction::class);
});

it('hides the delete action on view page for a ROLE_LIBRARY user', function (): void {
    $this->actingAs($this->member);
    $categorie = Categorie::factory()->create();

    livewire(ViewCategorie::class, ['record' => $categorie->id])
        ->assertActionHidden(DeleteAction::class);
});

it('renders the fiches relation manager with the categorys fiches', function (): void {
    $categorie = Categorie::factory()->create();
    $fiches = Fiche::factory(2)->create(['category_id' => $categorie->id]);

    livewire(FichesRelationManager::class, [
        'ownerRecord' => $categorie,
        'pageClass' => ViewCategorie::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords($fiches);
});

it('renders the children relation manager with subcategories', function (): void {
    $parent = Categorie::factory()->create();
    $children = Categorie::factory(2)->create(['parent_id' => $parent->id]);

    livewire(ChildrenRelationManager::class, [
        'ownerRecord' => $parent,
        'pageClass' => ViewCategorie::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords($children);
});
