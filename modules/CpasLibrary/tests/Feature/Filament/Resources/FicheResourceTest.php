<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\CreateFiche;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\EditFiche;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ListFiches;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\Pages\ViewFiche;
use AcMarche\CpasLibrary\Models\Categorie;
use AcMarche\CpasLibrary\Models\Fiche;
use AcMarche\CpasLibrary\Models\Tag;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('cpas-library-panel'));
    Storage::fake('cpas-library');

    $this->admin = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY_ADMIN->value]);
    $this->admin->roles()->attach($adminRole);

    $this->member = User::factory()->create(['username' => 'member1']);
    $memberRole = Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]);
    $this->member->roles()->attach($memberRole);

    $this->actingAs($this->admin);
});

it('renders the list, create, view and edit pages', function (): void {
    $fiche = Fiche::factory()->create();

    livewire(ListFiches::class)->assertOk();
    livewire(CreateFiche::class)->assertOk();
    livewire(ViewFiche::class, ['record' => $fiche->id])->assertOk();
    livewire(EditFiche::class, ['record' => $fiche->id])->assertOk();
});

it('lists fiches', function (): void {
    $fiches = Fiche::factory(3)->create();

    livewire(ListFiches::class)
        ->loadTable()
        ->assertCanSeeTableRecords($fiches);
});

it('has the expected table columns', function (string $column): void {
    livewire(ListFiches::class)->assertTableColumnExists($column);
})->with(['name', 'category.name', 'tags.name', 'userAdd', 'createdAt']);

it('filters fiches by category', function (): void {
    $categoryA = Categorie::factory()->create();
    $categoryB = Categorie::factory()->create();
    $fichesA = Fiche::factory(2)->create(['category_id' => $categoryA->id]);
    $fichesB = Fiche::factory(2)->create(['category_id' => $categoryB->id]);

    livewire(ListFiches::class)
        ->loadTable()
        ->filterTable('category_id', $categoryA->id)
        ->assertCanSeeTableRecords($fichesA)
        ->assertCanNotSeeTableRecords($fichesB);
});

it('filters fiches by has_rappel', function (): void {
    $withRappel = Fiche::factory()->create(['date_rappel' => now()->toDateString()]);
    $withoutRappel = Fiche::factory()->create(['date_rappel' => null]);

    livewire(ListFiches::class)
        ->loadTable()
        ->filterTable('has_rappel', true)
        ->assertCanSeeTableRecords([$withRappel])
        ->assertCanNotSeeTableRecords([$withoutRappel]);
});

it('creates a fiche and sets userAdd from auth user', function (): void {
    $categorie = Categorie::factory()->create();

    livewire(CreateFiche::class)
        ->fillForm([
            'name' => 'New fiche',
            'category_id' => $categorie->id,
            'type' => 'default',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Fiche::class, [
        'name' => 'New fiche',
        'category_id' => $categorie->id,
        'userAdd' => $this->admin->username,
    ]);
});

it('regenerates a slug from name + id after save when slug is empty', function (): void {
    $fiche = Fiche::factory()->create(['slug' => null, 'name' => 'Some article']);

    expect($fiche->fresh()->slug)->toBe('some-article-'.$fiche->id);
});

it('attaches tags via the form', function (): void {
    $categorie = Categorie::factory()->create();
    $tag = Tag::factory()->create();

    livewire(CreateFiche::class)
        ->fillForm([
            'name' => 'Tagged fiche',
            'category_id' => $categorie->id,
            'tags' => [$tag->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $fiche = Fiche::query()->where('name', 'Tagged fiche')->first();
    expect($fiche->tags->pluck('id')->all())->toContain($tag->id);
});

it('validates date_begin must be before_or_equal date_end', function (): void {
    $fiche = Fiche::factory()->create();

    livewire(EditFiche::class, ['record' => $fiche->id])
        ->fillForm([
            'date_begin' => '2026-06-10',
            'date_end' => '2026-06-01',
        ])
        ->call('save')
        ->assertHasFormErrors(['date_begin'])
        ->assertNotNotified();
});

it('validates the form data', function (array $data, array $errors): void {
    $fiche = Fiche::factory()->create();

    livewire(EditFiche::class, ['record' => $fiche->id])
        ->fillForm([
            'name' => $fiche->name,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
]);

it('allows a ROLE_LIBRARY user to edit a fiche they own', function (): void {
    $fiche = Fiche::factory()->create(['userAdd' => $this->member->username]);
    $this->actingAs($this->member);

    livewire(EditFiche::class, ['record' => $fiche->id])
        ->fillForm(['name' => 'Mine updated'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Fiche::class, [
        'id' => $fiche->id,
        'name' => 'Mine updated',
    ]);
});

it('forbids a ROLE_LIBRARY user from editing a fiche owned by someone else', function (): void {
    $fiche = Fiche::factory()->create(['userAdd' => 'someone-else']);
    $this->actingAs($this->member);

    livewire(EditFiche::class, ['record' => $fiche->id])
        ->assertForbidden();
});

it('allows an admin to edit any fiche', function (): void {
    $fiche = Fiche::factory()->create(['userAdd' => 'someone-else']);

    livewire(EditFiche::class, ['record' => $fiche->id])
        ->fillForm(['name' => 'Admin override'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('forbids a user without library role from listing fiches', function (): void {
    $stranger = User::factory()->create();
    $this->actingAs($stranger);

    livewire(ListFiches::class)->assertForbidden();
});

it('hides the download action when fileName is null', function (): void {
    $fiche = Fiche::factory()->create(['fileName' => null]);

    livewire(ViewFiche::class, ['record' => $fiche->id])
        ->assertActionHidden('download');
});

it('shows the download action when fileName is set', function (): void {
    $fiche = Fiche::factory()->create(['fileName' => 'doc.pdf']);

    livewire(ViewFiche::class, ['record' => $fiche->id])
        ->assertActionVisible('download');
});

it('downloads the file from the cpas-library disk', function (): void {
    Storage::disk('cpas-library')->put('fiches/doc.pdf', 'binary');
    $fiche = Fiche::factory()->create(['fileName' => 'doc.pdf']);

    livewire(ViewFiche::class, ['record' => $fiche->id])
        ->callAction('download')
        ->assertFileDownloaded('doc.pdf');
});

it('deletes a fiche from the view page as admin', function (): void {
    $fiche = Fiche::factory()->create();

    livewire(ViewFiche::class, ['record' => $fiche->id])
        ->callAction(DeleteAction::class)
        ->assertNotified();

    expect(Fiche::query()->find($fiche->id))->toBeNull();
});

it('hides the delete action for a non-owner ROLE_LIBRARY user', function (): void {
    $fiche = Fiche::factory()->create(['userAdd' => 'someone-else']);
    $this->actingAs($this->member);

    livewire(ListFiches::class)
        ->loadTable()
        ->assertActionHidden(TestAction::make(DeleteAction::class)->table($fiche));
});
