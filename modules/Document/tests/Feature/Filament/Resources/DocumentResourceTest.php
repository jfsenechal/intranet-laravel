<?php

declare(strict_types=1);

use AcMarche\Document\Filament\Resources\Documents\Pages\CreateDocument;
use AcMarche\Document\Filament\Resources\Documents\Pages\EditDocument;
use AcMarche\Document\Filament\Resources\Documents\Pages\ListDocuments;
use AcMarche\Document\Filament\Resources\Documents\Pages\ViewDocument;
use AcMarche\Document\Models\Category;
use AcMarche\Document\Models\Document;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('document-panel'));
    $this->user = User::factory()->create();
    $role = Role::factory()->create(['name' => 'ROLE_DOCUMENT_ADMIN']);
    $this->user->roles()->attach($role);
    $this->actingAs($this->user);
    Storage::fake('public');
});

it('can render the index page', function (): void {
    livewire(ListDocuments::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateDocument::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $document->name,
            'category_id' => $document->category_id,
        ]);
});

it('can render the view page', function (): void {
    $document = Document::factory()->create();

    livewire(ViewDocument::class, ['record' => $document->id])
        ->assertOk();
});

it('can list documents', function (): void {
    $documents = Document::factory(3)->create();

    livewire(ListDocuments::class)
        ->loadTable()
        ->assertCanSeeTableRecords($documents);
});

it('has table columns', function (string $column): void {
    livewire(ListDocuments::class)
        ->assertTableColumnExists($column);
})->with(['name', 'category.name']);

it('can sort by name', function (): void {
    $documents = Document::factory(5)->create();

    livewire(ListDocuments::class)
        ->loadTable()
        ->sortTable('name')
        ->assertCanSeeTableRecords($documents)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($documents);
});

it('can search documents by name', function (): void {
    $documents = Document::factory(5)->create();

    $search = $documents->first()->name;

    livewire(ListDocuments::class)
        ->loadTable()
        ->searchTable($search)
        ->assertCanSeeTableRecords($documents->where('name', $search))
        ->assertCanNotSeeTableRecords($documents->where('name', '!=', $search));
});

it('can filter documents by category', function (): void {
    $categoryA = Category::factory()->create();
    $categoryB = Category::factory()->create();

    $documentsA = Document::factory(2)->create(['category_id' => $categoryA->id]);
    $documentsB = Document::factory(2)->create(['category_id' => $categoryB->id]);

    livewire(ListDocuments::class)
        ->loadTable()
        ->filterTable('category_id', $categoryA->id)
        ->assertCanSeeTableRecords($documentsA)
        ->assertCanNotSeeTableRecords($documentsB);
});

it('can create a document', function (): void {
    $category = Category::factory()->create();
    $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

    livewire(CreateDocument::class)
        ->fillForm([
            'name' => 'My Document',
            'content' => '<p>Some content</p>',
            'category_id' => $category->id,
            'file_path' => $file,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Document::class, [
        'name' => 'My Document',
        'category_id' => $category->id,
    ]);
});

it('can update a document', function (): void {
    $document = Document::factory()->create();
    Storage::disk('public')->put($document->file_path, 'dummy content');
    $newCategory = Category::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            'name' => 'Updated Name',
            'category_id' => $newCategory->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Document::class, [
        'id' => $document->id,
        'name' => 'Updated Name',
        'category_id' => $newCategory->id,
    ]);
});

it('can delete a document', function (): void {
    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted($document);
});

it('can bulk delete documents', function (): void {
    $documents = Document::factory(3)->create();

    livewire(ListDocuments::class)
        ->loadTable()
        ->assertCanSeeTableRecords($documents)
        ->selectTableRecords($documents)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($documents);

    $documents->each(fn (Document $document) => $this->assertSoftDeleted($document));
});

it('can delete a document from view page', function (): void {
    $document = Document::factory()->create();

    livewire(ViewDocument::class, ['record' => $document->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted($document);
});

it('validates the form data', function (array $data, array $errors): void {
    $document = Document::factory()->create();

    livewire(EditDocument::class, ['record' => $document->id])
        ->fillForm([
            'name' => $document->name,
            'category_id' => $document->category_id,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`category_id` is required' => [['category_id' => null], ['category_id' => 'required']],
]);

it('prevents a regular user from editing a document they do not own', function (): void {
    $document = Document::factory()->create();
    $document->update(['user_add' => 'other-user']);

    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    livewire(EditDocument::class, ['record' => $document->id])
        ->assertForbidden();
});

it('prevents a regular user from deleting a document they do not own', function (): void {
    $document = Document::factory()->create();
    $document->update(['user_add' => 'other-user']);

    $regularUser = User::factory()->create();
    $this->actingAs($regularUser);

    livewire(ViewDocument::class, ['record' => $document->id])
        ->assertActionHidden(DeleteAction::class);
});

it('allows a document creator to edit their own document', function (): void {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $document = Document::factory()->create(['user_add' => $creator->username]);
    Storage::disk('public')->put($document->file_path, 'dummy content');

    livewire(EditDocument::class, ['record' => $document->id])
        ->assertOk()
        ->fillForm([
            'name' => 'Updated by creator',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Document::class, [
        'id' => $document->id,
        'name' => 'Updated by creator',
    ]);
});

it('allows a document creator to delete their own document', function (): void {
    $creator = User::factory()->create();
    $this->actingAs($creator);
    $document = Document::factory()->create(['user_add' => $creator->username]);

    livewire(ViewDocument::class, ['record' => $document->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted($document);
});
