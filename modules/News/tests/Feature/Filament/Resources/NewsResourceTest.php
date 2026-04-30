<?php

declare(strict_types=1);

use AcMarche\News\Filament\Resources\News\Pages\CreateNews;
use AcMarche\News\Filament\Resources\News\Pages\EditNews;
use AcMarche\News\Filament\Resources\News\Pages\ListNews;
use AcMarche\News\Filament\Resources\News\Pages\ViewNews;
use AcMarche\News\Models\Category;
use AcMarche\News\Models\News;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Mail::fake();
    Filament::setCurrentPanel(Filament::getPanel('news'));
    auth()->user()->update(['is_administrator' => true]);
    $this->category = Category::factory()->create();

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.news.resources.news.index')) {
        Route::get('/news', fn (): string => '')->name('filament.news.resources.news.index');
        Route::get('/news/create', fn (): string => '')->name('filament.news.resources.news.create');
        Route::get('/news/{record}/edit', fn (): string => '')->name('filament.news.resources.news.edit');
        Route::get('/news/{record}', fn (): string => '')->name('filament.news.resources.news.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListNews::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateNews::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $news = News::factory()->create();

    livewire(EditNews::class, [
        'record' => $news->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $news->name,
        ]);
});

it('can render the view page', function (): void {
    $news = News::factory()->create();

    livewire(ViewNews::class, [
        'record' => $news->id,
    ])
        ->assertOk();
});

it('has column', function (string $column): void {
    livewire(ListNews::class)
        ->assertTableColumnExists($column);
})->with(['name', 'category.name']);

it('can render column', function (string $column): void {
    livewire(ListNews::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'category.name']);

it('can filter by category', function (): void {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $news1 = News::factory()->create(['category_id' => $cat1->id]);
    $news2 = News::factory()->create(['category_id' => $cat2->id]);

    livewire(ListNews::class)
        ->loadTable()
        ->filterTable('category_id', $cat1->id)
        ->assertCanSeeTableRecords([$news1])
        ->assertCanNotSeeTableRecords([$news2]);
});

it('can filter archived news', function (): void {
    $archived = News::factory()->create(['archive' => true]);
    $notArchived = News::factory()->create(['archive' => false]);

    // NewsTables excludes archived by default, so archived should not appear
    livewire(ListNews::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$notArchived])
        ->assertCanNotSeeTableRecords([$archived]);
});

it('can load the create form', function (): void {
    livewire(CreateNews::class)
        ->assertSchemaComponentExists('name')
        ->assertSchemaComponentExists('content')
        ->assertSchemaComponentExists('category_id');
});

it('can load the edit form with data', function (): void {
    $news = News::factory()->create();

    livewire(EditNews::class, [
        'record' => $news->id,
    ])
        ->assertSchemaStateSet([
            'name' => $news->name,
        ]);
});

it('can delete a news item', function (): void {
    $news = News::factory()->create();

    livewire(ViewNews::class, [
        'record' => $news->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($news);
});

it('can bulk delete news items', function (): void {
    $newsItems = News::factory(5)->create();

    livewire(ListNews::class)
        ->loadTable()
        ->assertCanSeeTableRecords($newsItems)
        ->selectTableRecords($newsItems)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($newsItems);

    $newsItems->each(fn (News $news) => assertDatabaseMissing($news));
});

it('archive action exists on view page', function (): void {
    $news = News::factory()->create(['archive' => false]);

    livewire(ViewNews::class, [
        'record' => $news->id,
    ])
        ->assertActionExists('archive');
});

it('validates the form data', function (array $data, array $errors): void {
    $newsData = News::factory()->make();

    livewire(CreateNews::class)
        ->fillForm([
            'name' => $newsData->name,
            'content' => $newsData->content,
            'category_id' => $this->category->id,
            'department' => 'common',
            'end_date' => '2026-04-14',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`category_id` is required' => [['category_id' => null], ['category_id' => 'required']],
    '`department` is required' => [['department' => null], ['department' => 'required']],
    '`end_date` is required' => [['end_date' => null], ['end_date' => 'required']],
]);
