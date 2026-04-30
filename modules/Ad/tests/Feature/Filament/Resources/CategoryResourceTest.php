<?php

declare(strict_types=1);

use AcMarche\Ad\Filament\Resources\Categories\Pages\CreateCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\EditCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\ListCategory;
use AcMarche\Ad\Filament\Resources\Categories\Pages\ViewCategory;
use AcMarche\Ad\Filament\Resources\Categories\RelationManagers\NewsRelationManager;
use AcMarche\Ad\Models\Ad;
use AcMarche\Ad\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Mail::fake();
    Filament::setCurrentPanel(Filament::getPanel('ad'));
    auth()->user()->update(['is_administrator' => true]);

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.ad.resources.categories.index')) {
        Route::get('/categories', fn (): string => '')->name('filament.ad.resources.categories.index');
        Route::get('/categories/create', fn (): string => '')->name('filament.ad.resources.categories.create');
        Route::get('/categories/{record}/edit', fn (): string => '')->name('filament.ad.resources.categories.edit');
        Route::get('/categories/{record}', fn (): string => '')->name('filament.ad.resources.categories.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListCategory::class)
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
    livewire(ListCategory::class)
        ->assertTableColumnExists($column);
})->with(['name', 'color', 'news_count']);

it('can render column', function (string $column): void {
    livewire(ListCategory::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'color']);

it('can load the create form', function (): void {
    livewire(CreateCategory::class)
        ->assertSchemaComponentExists('name');
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

    livewire(ListCategory::class)
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

    livewire(ListCategory::class)
        ->loadTable()
        ->searchTable('Development')
        ->assertCanSeeTableRecords([$category1])
        ->assertCanNotSeeTableRecords([$category2]);
});

it('displays table actions on list page', function (): void {
    $category = Category::factory()->create();

    livewire(ListCategory::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$category])
        ->assertTableActionExists('view')
        ->assertTableActionExists('edit');
});

it('displays delete action on view page', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->assertActionExists('delete');
});

it('displays ad count on list page', function (): void {
    $category = Category::factory()->create();
    Ad::factory(3)->create(['category_id' => $category->id]);

    livewire(ListCategory::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$category])
        ->assertTableColumnStateSet('news_count', 3, $category);
});

it('can render the ad relation manager on view page', function (): void {
    $category = Category::factory()->create();

    livewire(NewsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk();
});

it('lists related ad in the relation manager', function (): void {
    $category = Category::factory()->create();
    $classifiedAd = Ad::factory(2)->create(['category_id' => $category->id]);
    $otherNews = Ad::factory()->create();

    livewire(NewsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->loadTable()
        ->assertCanSeeTableRecords($classifiedAd)
        ->assertCanNotSeeTableRecords([$otherNews]);
});

it('denies create action for regular user', function (): void {
    auth()->user()->update(['is_administrator' => false]);

    livewire(ListCategory::class)
        ->assertActionHidden('create');
});

it('denies edit action for regular user', function (): void {
    auth()->user()->update(['is_administrator' => false]);
    $category = Category::factory()->create();

    livewire(ListCategory::class)
        ->loadTable()
        ->assertTableActionHidden('edit', $category);
});
