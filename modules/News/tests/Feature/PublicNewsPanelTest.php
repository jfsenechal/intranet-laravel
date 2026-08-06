<?php

declare(strict_types=1);

use AcMarche\News\Filament\Resources\Categories\CategoryResource;
use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\News\Filament\Resources\News\Pages\ListNews;
use AcMarche\News\Filament\Resources\News\Pages\ViewNews;
use AcMarche\News\Models\Category;
use AcMarche\News\Models\News;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('news-panel'));
    Mail::fake();
});

it('lets a guest browse the news panel', function (string $url): void {
    News::factory()->create();
    Category::factory()->create();

    auth()->logout();
    $this->assertGuest();

    $this->get($url)->assertOk();
})->with([
    'news index' => fn (): string => NewsResource::getUrl('index'),
    'categories index' => fn (): string => CategoryResource::getUrl('index'),
]);

it('shows a guest an article and its category', function (): void {
    $category = Category::factory()->create(['name' => 'Communication']);
    $news = News::factory()->create([
        'name' => 'Une actualité publique',
        'category_id' => $category->id,
    ]);

    auth()->logout();

    // The table defers loading, so records land on the second render.
    livewire(ListNews::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$news])
        ->assertSee('Une actualité publique');

    $this->get(NewsResource::getUrl('view', ['record' => $news]))
        ->assertOk()
        ->assertSee('Une actualité publique');

    $this->get(CategoryResource::getUrl('view', ['record' => $category]))
        ->assertOk()
        ->assertSee('Communication');
});

it('lets a guest search the news list from the search box', function (): void {
    $wanted = News::factory()->create(['name' => 'Travaux rue de la Station']);
    $other = News::factory()->create(['name' => 'Horaires de la piscine']);

    auth()->logout();

    livewire(ListNews::class)
        ->assertOk()
        ->loadTable()
        ->searchTable('Travaux')
        ->assertCanSeeTableRecords([$wanted])
        ->assertCanNotSeeTableRecords([$other]);
});

it('lets a guest search the news list by title', function (): void {
    $wanted = News::factory()->create(['name' => 'Travaux rue de la Station']);
    $other = News::factory()->create(['name' => 'Horaires de la piscine']);

    auth()->logout();

    livewire(ListNews::class)
        ->assertOk()
        ->loadTable()
        ->filterTable('name', ['name' => 'Travaux'])
        ->assertCanSeeTableRecords([$wanted])
        ->assertCanNotSeeTableRecords([$other]);
});

it('lets a guest filter the news list by category', function (): void {
    $category = Category::factory()->create(['name' => 'Travaux']);
    $wanted = News::factory()->create(['category_id' => $category->id]);
    $other = News::factory()->create();

    auth()->logout();

    livewire(ListNews::class)
        ->assertOk()
        ->loadTable()
        ->filterTable('category_id', $category->id)
        ->assertCanSeeTableRecords([$wanted])
        ->assertCanNotSeeTableRecords([$other]);
});

it('denies a guest the write pages', function (string $page): void {
    $news = News::factory()->create();

    auth()->logout();

    $this->get(NewsResource::getUrl($page, ['record' => $news]))
        ->assertForbidden();
})->with(['create', 'edit']);

it('denies a guest every write ability on the resources', function (): void {
    $news = News::factory()->create();
    $category = Category::factory()->create();

    auth()->logout();

    expect(NewsResource::canCreate())->toBeFalse()
        ->and(NewsResource::canEdit($news))->toBeFalse()
        ->and(NewsResource::canDelete($news))->toBeFalse()
        ->and(NewsResource::canDeleteAny())->toBeFalse()
        ->and(NewsResource::canReorder())->toBeFalse()
        ->and(CategoryResource::canCreate())->toBeFalse()
        ->and(CategoryResource::canEdit($category))->toBeFalse()
        ->and(CategoryResource::canDeleteAny())->toBeFalse();
});

it('hides the archive action from a guest', function (): void {
    $news = News::factory()->create(['archive' => false]);

    auth()->logout();

    $this->get(NewsResource::getUrl('view', ['record' => $news]))
        ->assertOk()
        ->assertDontSee('Archiver');

    livewire(ViewNews::class, ['record' => $news->id])
        ->assertActionHidden('archive');
});

it('still lets a signed in user reach the write pages', function (): void {
    $news = News::factory()->create(['archive' => false]);

    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $this->get(NewsResource::getUrl('index'))->assertOk();
    $this->get(NewsResource::getUrl('create'))->assertOk();
    $this->get(NewsResource::getUrl('edit', ['record' => $news]))->assertOk();

    expect(NewsResource::canDeleteAny())->toBeTrue();

    livewire(ViewNews::class, ['record' => $news->id])
        ->assertActionVisible('archive');
});
