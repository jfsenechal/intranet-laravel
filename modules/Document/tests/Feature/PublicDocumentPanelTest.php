<?php

declare(strict_types=1);

use AcMarche\Document\Filament\Resources\Categories\CategoryResource;
use AcMarche\Document\Filament\Resources\Documents\DocumentResource;
use AcMarche\Document\Filament\Resources\Documents\Pages\ListDocuments;
use AcMarche\Document\Models\Category;
use AcMarche\Document\Models\Document;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('document-panel'));
    Storage::fake('public');
});

it('lets a guest browse the document panel', function (string $url): void {
    Document::factory()->create();
    Category::factory()->create();

    auth()->logout();
    $this->assertGuest();

    $this->get($url)->assertOk();
})->with([
    'documents index' => fn (): string => DocumentResource::getUrl('index'),
    'categories index' => fn (): string => CategoryResource::getUrl('index'),
]);

it('shows a guest a document and its category', function (): void {
    $category = Category::factory()->create(['name' => 'Règlements']);
    $document = Document::factory()->create([
        'name' => 'Règlement de police',
        'category_id' => $category->id,
    ]);

    auth()->logout();

    // The table defers loading, so records land on the second render.
    livewire(ListDocuments::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$document])
        ->assertSee('Règlement de police');

    $this->get(DocumentResource::getUrl('view', ['record' => $document]))
        ->assertOk()
        ->assertSee('Règlement de police');

    $this->get(CategoryResource::getUrl('view', ['record' => $category]))
        ->assertOk()
        ->assertSee('Règlements');
});

it('lets a guest search the document list', function (): void {
    $wanted = Document::factory()->create(['name' => 'Formulaire de mutation']);
    $other = Document::factory()->create(['name' => 'Plan de mobilité']);

    auth()->logout();

    livewire(ListDocuments::class)
        ->assertOk()
        ->loadTable()
        ->searchTable('mutation')
        ->assertCanSeeTableRecords([$wanted])
        ->assertCanNotSeeTableRecords([$other]);
});

it('denies a guest the write pages', function (string $page): void {
    $document = Document::factory()->create();

    auth()->logout();

    $this->get(DocumentResource::getUrl($page, ['record' => $document]))
        ->assertForbidden();
})->with(['create', 'edit']);

it('denies a guest every write ability on the resources', function (): void {
    $document = Document::factory()->create();
    $category = Category::factory()->create();

    auth()->logout();

    expect(DocumentResource::canCreate())->toBeFalse()
        ->and(DocumentResource::canEdit($document))->toBeFalse()
        ->and(DocumentResource::canDelete($document))->toBeFalse()
        ->and(DocumentResource::canDeleteAny())->toBeFalse()
        ->and(DocumentResource::canReorder())->toBeFalse()
        ->and(CategoryResource::canCreate())->toBeFalse()
        ->and(CategoryResource::canEdit($category))->toBeFalse()
        ->and(CategoryResource::canDeleteAny())->toBeFalse();
});

it('denies a regular user to bulk delete documents', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));

    expect(DocumentResource::canDeleteAny())->toBeFalse()
        ->and(CategoryResource::canDeleteAny())->toBeFalse();
});

it('still lets a signed in user reach the write pages', function (): void {
    $document = Document::factory()->create();

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->get(DocumentResource::getUrl('index'))->assertOk();
    $this->get(DocumentResource::getUrl('create'))->assertOk();
    $this->get(DocumentResource::getUrl('edit', ['record' => $document]))->assertOk();

    expect(DocumentResource::canDeleteAny())->toBeTrue();
});
