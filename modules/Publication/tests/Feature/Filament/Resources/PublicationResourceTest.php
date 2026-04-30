<?php

declare(strict_types=1);

use AcMarche\Publication\Filament\Resources\Publications\Pages\CreatePublication;
use AcMarche\Publication\Filament\Resources\Publications\Pages\EditPublication;
use AcMarche\Publication\Filament\Resources\Publications\Pages\ListPublications;
use AcMarche\Publication\Filament\Resources\Publications\Pages\ViewPublication;
use AcMarche\Publication\Models\Category;
use AcMarche\Publication\Models\Publication;
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
    if (! Route::getRoutes()->getByName('filament.publication-panel.resources.publications.index')) {
        Route::get('/publications', fn (): string => '')->name('filament.publication-panel.resources.publications.index');
        Route::get('/publications/create', fn (): string => '')->name('filament.publication-panel.resources.publications.create');
        Route::get('/publications/{record}/edit', fn (): string => '')->name('filament.publication-panel.resources.publications.edit');
        Route::get('/publications/{record}', fn (): string => '')->name('filament.publication-panel.resources.publications.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListPublications::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreatePublication::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $publication = Publication::factory()->create();

    livewire(EditPublication::class, [
        'record' => $publication->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $publication->name,
            'url' => $publication->url,
        ]);
});

it('can render the view page', function (): void {
    $publication = Publication::factory()->create();

    livewire(ViewPublication::class, [
        'record' => $publication->id,
    ])
        ->assertOk();
});

it('has column', function (string $column): void {
    livewire(ListPublications::class)
        ->assertTableColumnExists($column);
})->with(['name', 'category.name', 'expire_date']);

it('can render column', function (string $column): void {
    livewire(ListPublications::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'category.name', 'expire_date']);

it('can load the create form', function (): void {
    livewire(CreatePublication::class)
        ->assertSchemaComponentExists('name')
        ->assertSchemaComponentExists('url')
        ->assertSchemaComponentExists('category_id')
        ->assertSchemaComponentExists('expire_date');
});

it('can load the edit form with data', function (): void {
    $publication = Publication::factory()->create();

    livewire(EditPublication::class, [
        'record' => $publication->id,
    ])
        ->assertSchemaStateSet([
            'name' => $publication->name,
            'url' => $publication->url,
        ]);
});

it('can delete a publication', function (): void {
    $publication = Publication::factory()->create();

    livewire(ViewPublication::class, [
        'record' => $publication->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Publication::class, ['id' => $publication->id]);
});

it('can bulk delete publications', function (): void {
    $publications = Publication::factory(5)->create();

    livewire(ListPublications::class)
        ->loadTable()
        ->assertCanSeeTableRecords($publications)
        ->selectTableRecords($publications)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($publications);

    $publications->each(fn (Publication $publication) => assertDatabaseMissing(Publication::class, ['id' => $publication->id]));
});

it('can search publications by name', function (): void {
    $publication1 = Publication::factory()->create(['name' => 'Lorem ipsum dolor']);
    $publication2 = Publication::factory()->create(['name' => 'Consectetur adipiscing']);

    livewire(ListPublications::class)
        ->loadTable()
        ->searchTable('Lorem')
        ->assertCanSeeTableRecords([$publication1])
        ->assertCanNotSeeTableRecords([$publication2]);
});

it('can filter by category', function (): void {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $pub1 = Publication::factory()->create(['category_id' => $cat1->id]);
    $pub2 = Publication::factory()->create(['category_id' => $cat2->id]);

    livewire(ListPublications::class)
        ->loadTable()
        ->filterTable('category', $cat1->id)
        ->assertCanSeeTableRecords([$pub1])
        ->assertCanNotSeeTableRecords([$pub2]);
});

it('displays table actions on list page', function (): void {
    $publication = Publication::factory()->create();

    livewire(ListPublications::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$publication])
        ->assertTableActionExists('view');
});

it('displays delete action on view page', function (): void {
    $publication = Publication::factory()->create();

    livewire(ViewPublication::class, [
        'record' => $publication->id,
    ])
        ->assertActionExists('delete');
});

it('validates the form data', function (array $data, array $errors): void {
    livewire(CreatePublication::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`url` is required' => [['url' => null], ['url' => 'required']],
    '`url` must be a valid URL' => [['url' => 'not-a-url'], ['url' => 'url']],
    '`url` is max 255 characters' => [['url' => 'https://'.Str::random(250).'.com'], ['url' => 'max']],
]);
