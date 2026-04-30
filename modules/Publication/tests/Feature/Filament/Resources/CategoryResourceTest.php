<?php

declare(strict_types=1);

use AcMarche\Publication\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Publication\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Publication\Filament\Resources\Categories\Pages\ListCategories;
use AcMarche\Publication\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Publication\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('publication-panel'));

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.publication-panel.resources.categories.index')) {
        Route::get('/categories', fn (): string => '')->name('filament.publication-panel.resources.categories.index');
        Route::get('/categories/create', fn (): string => '')->name('filament.publication-panel.resources.categories.create');
        Route::get('/categories/{record}/edit', fn (): string => '')->name('filament.publication-panel.resources.categories.edit');
        Route::get('/categories/{record}', fn (): string => '')->name('filament.publication-panel.resources.categories.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListCategories::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateCategory::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $category = Category::factory()->create();

    livewire(EditCategory::class, [
        'record' => $category->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $category->name,
        ]);
});

it('can render the view page', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->assertOk();
});

it('has column', function (string $column): void {
    livewire(ListCategories::class)
        ->assertTableColumnExists($column);
})->with(['name', 'publications_count']);

it('can render column', function (string $column): void {
    livewire(ListCategories::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'publications_count']);

it('can load the create form', function (): void {
    livewire(CreateCategory::class)
        ->assertSchemaComponentExists('name')
        ->assertSchemaComponentExists('url')
        ->assertSchemaComponentExists('wpCategoryId');
});

it('can load the edit form with data', function (): void {
    $category = Category::factory()->create();

    livewire(EditCategory::class, [
        'record' => $category->id,
    ])
        ->assertSchemaStateSet([
            'name' => $category->name,
        ]);
});

it('can delete a category', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Category::class, ['id' => $category->id]);
});

it('can bulk delete categories', function (): void {
    $categories = Category::factory(5)->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->assertCanSeeTableRecords($categories)
        ->selectTableRecords($categories)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($categories);

    $categories->each(fn (Category $category) => assertDatabaseMissing(Category::class, ['id' => $category->id]));
});

it('can search categories by name', function (): void {
    $category1 = Category::factory()->create(['name' => 'Development']);
    $category2 = Category::factory()->create(['name' => 'Design']);

    livewire(ListCategories::class)
        ->loadTable()
        ->searchTable('Development')
        ->assertCanSeeTableRecords([$category1])
        ->assertCanNotSeeTableRecords([$category2]);
});

it('displays table actions on list page', function (): void {
    $category = Category::factory()->create();

    livewire(ListCategories::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$category])
        ->assertTableActionExists('view');
});

it('displays delete action on view page', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->assertActionExists('delete');
});

it('validates the form data', function (array $data, array $errors): void {
    livewire(CreateCategory::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`url` is required' => [['url' => null], ['url' => 'required']],
    '`url` must be a valid URL' => [['url' => 'not-a-url'], ['url' => 'url']],
    '`url` is max 255 characters' => [['url' => 'https://'.Str::random(250).'.com'], ['url' => 'max']],
    '`wpCategoryId` is required' => [['wpCategoryId' => null], ['wpCategoryId' => 'required']],
    '`wpCategoryId` must be an integer' => [['wpCategoryId' => 'not-a-number'], ['wpCategoryId' => 'integer']],
]);
