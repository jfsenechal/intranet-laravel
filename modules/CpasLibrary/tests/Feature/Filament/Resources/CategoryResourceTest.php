<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ListCategories;
use AcMarche\CpasLibrary\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\ChildrenRelationManager;
use AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\FichesRelationManager;
use AcMarche\CpasLibrary\Models\Category;
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
    $categorie = Category::factory()->create();

    livewire(ListCategories::class)->assertOk();
    livewire(CreateCategory::class)->assertOk();
    livewire(ViewCategory::class, ['record' => $categorie->id])->assertOk();
    livewire(EditCategory::class, ['record' => $categorie->id])->assertOk();
});

it('lists categories', function (): void {
    $categories = Category::factory(3)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->assertCanSeeTableRecords($categories);
});

it('has the expected table columns', function (string $column): void {
    livewire(ListCategories::class)->assertTableColumnExists($column);
})->with(['name', 'parent.name', 'public', 'fiches_count']);

it('searches by name', function (): void {
    $categories = Category::factory(3)->create();
    $needle = $categories->first()->name;

    livewire(ListCategories::class)
        ->loadTable()
        ->searchTable($needle)
        ->assertCanSeeTableRecords($categories->where('name', $needle))
        ->assertCanNotSeeTableRecords($categories->where('name', '!=', $needle));
});

it('filters by parent', function (): void {
    $parent = Category::factory()->create();
    $children = Category::factory(2)->create(['parent_id' => $parent->id]);
    $orphans = Category::factory(2)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->filterTable('parent_id', $parent->id)
        ->assertCanSeeTableRecords($children)
        ->assertCanNotSeeTableRecords($orphans);
});

it('creates a categorie via the form', function (): void {
    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Aide sociale',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $categorie = Category::query()->where('name', 'Aide sociale')->first();

    expect($categorie->departments)->toBe([DepartmentEnum::CPAS->value]);
});

it('updates a categorie via the form', function (): void {
    $categorie = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm(['name' => 'Renamed'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Category::class, [
        'id' => $categorie->id,
        'name' => 'Renamed',
    ]);
});

it('forces the departments to CPAS', function (): void {
    $categorie = Category::factory()->create(['departments' => [DepartmentEnum::VILLE->value]]);

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm(['name' => 'Kept'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($categorie->refresh()->departments)->toBe([DepartmentEnum::CPAS->value]);
});

it('deletes a categorie from the view page', function (): void {
    $categorie = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $categorie->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    assertDatabaseMissing($categorie);
});

it('validates the form data', function (array $data, array $errors): void {
    $categorie = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm([
            'name' => 'Valid name',
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
]);

it('excludes the current record from the parent_id options', function (): void {
    $categorie = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm(['parent_id' => $categorie->id])
        ->call('save')
        ->assertHasFormErrors(['parent_id']);
});

it('only offers root categories as a parent', function (): void {
    $root = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $root->id]);
    $categorie = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm(['parent_id' => $child->id])
        ->call('save')
        ->assertHasFormErrors(['parent_id']);

    livewire(EditCategory::class, ['record' => $categorie->id])
        ->fillForm(['parent_id' => $root->id])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('forbids a user without a library role from listing categories', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListCategories::class)->assertForbidden();
});

it('forbids a ROLE_LIBRARY user from creating a categorie', function (): void {
    $this->actingAs($this->member);

    livewire(CreateCategory::class)->assertForbidden();
});

it('hides the create header action for a ROLE_LIBRARY user', function (): void {
    $this->actingAs($this->member);

    livewire(ListCategories::class)->assertActionHidden(CreateAction::class);
});

it('hides the delete action on view page for a ROLE_LIBRARY user', function (): void {
    $this->actingAs($this->member);
    $categorie = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $categorie->id])
        ->assertActionHidden(DeleteAction::class);
});

it('renders the fiches relation manager with the categorys fiches', function (): void {
    $categorie = Category::factory()->create();
    $fiches = Fiche::factory(2)->create(['category_id' => $categorie->id]);

    livewire(FichesRelationManager::class, [
        'ownerRecord' => $categorie,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords($fiches);
});

it('renders the children relation manager with subcategories', function (): void {
    $parent = Category::factory()->create();
    $children = Category::factory(2)->create(['parent_id' => $parent->id]);

    livewire(ChildrenRelationManager::class, [
        'ownerRecord' => $parent,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords($children);
});
