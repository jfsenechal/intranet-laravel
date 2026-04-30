<?php

declare(strict_types=1);

use AcMarche\Ad\Filament\Resources\Ad\Pages\CreateClassifiedAd;
use AcMarche\Ad\Filament\Resources\Ad\Pages\EditClassifiedAd;
use AcMarche\Ad\Filament\Resources\Ad\Pages\ListClassifiedAd;
use AcMarche\Ad\Filament\Resources\Ad\Pages\ViewClassifiedAd;
use AcMarche\Ad\Models\Category;
use AcMarche\Ad\Models\ClassifiedAd;
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
    Filament::setCurrentPanel(Filament::getPanel('ad'));
    auth()->user()->update(['is_administrator' => true]);
    $this->category = Category::factory()->create();

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.ad.resources.ad.index')) {
        Route::get('/ad', fn (): string => '')->name('filament.ad.resources.ad.index');
        Route::get('/ad/create', fn (): string => '')->name('filament.ad.resources.ad.create');
        Route::get('/ad/{record}/edit', fn (): string => '')->name('filament.ad.resources.ad.edit');
        Route::get('/ad/{record}', fn (): string => '')->name('filament.ad.resources.ad.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListClassifiedAd::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateClassifiedAd::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    livewire(EditClassifiedAd::class, [
        'record' => $classifiedAd->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $classifiedAd->name,
        ]);
});

it('can render the view page', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    livewire(ViewClassifiedAd::class, [
        'record' => $classifiedAd->id,
    ])
        ->assertOk();
});

it('has column', function (string $column): void {
    livewire(ListClassifiedAd::class)
        ->assertTableColumnExists($column);
})->with(['name', 'category.name']);

it('can render column', function (string $column): void {
    livewire(ListClassifiedAd::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'category.name']);

it('can filter by category', function (): void {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $news1 = ClassifiedAd::factory()->create(['category_id' => $cat1->id]);
    $news2 = ClassifiedAd::factory()->create(['category_id' => $cat2->id]);

    livewire(ListClassifiedAd::class)
        ->loadTable()
        ->filterTable('category_id', $cat1->id)
        ->assertCanSeeTableRecords([$news1])
        ->assertCanNotSeeTableRecords([$news2]);
});

it('can filter archived ad', function (): void {
    $archived = ClassifiedAd::factory()->create(['archive' => true]);
    $notArchived = ClassifiedAd::factory()->create(['archive' => false]);

    // NewsTables excludes archived by default, so archived should not appear
    livewire(ListClassifiedAd::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$notArchived])
        ->assertCanNotSeeTableRecords([$archived]);
});

it('can load the create form', function (): void {
    livewire(CreateClassifiedAd::class)
        ->assertSchemaComponentExists('name')
        ->assertSchemaComponentExists('content')
        ->assertSchemaComponentExists('category_id');
});

it('can load the edit form with data', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    livewire(EditClassifiedAd::class, [
        'record' => $classifiedAd->id,
    ])
        ->assertSchemaStateSet([
            'name' => $classifiedAd->name,
        ]);
});

it('can delete a ad item', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create();

    livewire(ViewClassifiedAd::class, [
        'record' => $classifiedAd->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($classifiedAd);
});

it('can bulk delete ad items', function (): void {
    $newsItems = ClassifiedAd::factory(5)->create();

    livewire(ListClassifiedAd::class)
        ->loadTable()
        ->assertCanSeeTableRecords($newsItems)
        ->selectTableRecords($newsItems)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($newsItems);

    $newsItems->each(fn (ClassifiedAd $classifiedAd) => assertDatabaseMissing($classifiedAd));
});

it('archive action exists on view page', function (): void {
    $classifiedAd = ClassifiedAd::factory()->create(['archive' => false]);

    livewire(ViewClassifiedAd::class, [
        'record' => $classifiedAd->id,
    ])
        ->assertActionExists('archive');
});

it('validates the form data', function (array $data, array $errors): void {
    $newsData = ClassifiedAd::factory()->make();

    livewire(CreateClassifiedAd::class)
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
