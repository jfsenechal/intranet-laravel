<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages\CreateBudgetArticle;
use AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages\EditBudgetArticle;
use AcMarche\Mileage\Filament\Resources\BudgetArticles\Pages\ListBudgetArticles;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.mileage-panel.resources.budget-articles.index')) {
        Route::get('/budget-articles', fn (): string => '')->name('filament.mileage-panel.resources.budget-articles.index');
        Route::get('/budget-articles/create', fn (): string => '')->name('filament.mileage-panel.resources.budget-articles.create');
        Route::get('/budget-articles/{record}/edit', fn (): string => '')->name('filament.mileage-panel.resources.budget-articles.edit');
    }

    $this->user = User::factory()->create(['is_administrator' => true]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => $this->user->username]);
    $this->actingAs($this->user);
});

it('can render the index page', function (): void {
    livewire(ListBudgetArticles::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateBudgetArticle::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $budgetArticle = BudgetArticle::factory()->create();

    livewire(EditBudgetArticle::class, ['record' => $budgetArticle->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $budgetArticle->name,
            'functional_code' => $budgetArticle->functional_code,
            'economic_code' => $budgetArticle->economic_code,
        ]);
});

it('can list budget articles', function (): void {
    $articles = BudgetArticle::factory(5)->create();

    livewire(ListBudgetArticles::class)
        ->loadTable()
        ->assertCanSeeTableRecords($articles);
});

it('has table columns', function (string $column): void {
    livewire(ListBudgetArticles::class)
        ->assertTableColumnExists($column);
})->with(['name', 'department', 'functional_code', 'economic_code']);

it('can render table column', function (string $column): void {
    livewire(ListBudgetArticles::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'department', 'functional_code', 'economic_code']);

it('can sort by name', function (): void {
    $articles = BudgetArticle::factory(3)->create();

    livewire(ListBudgetArticles::class)
        ->loadTable()
        ->sortTable('name')
        ->assertCanSeeTableRecords($articles->sortBy('name'), inOrder: true);
});

it('can search budget articles by name', function (): void {
    $article1 = BudgetArticle::factory()->create(['name' => 'Budget Article One']);
    $article2 = BudgetArticle::factory()->create(['name' => 'Budget Article Two']);

    livewire(ListBudgetArticles::class)
        ->loadTable()
        ->searchTable('One')
        ->assertCanSeeTableRecords([$article1])
        ->assertCanNotSeeTableRecords([$article2]);
});

it('can create a budget article', function (): void {
    $article = BudgetArticle::factory()->make();

    livewire(CreateBudgetArticle::class)
        ->fillForm([
            'name' => $article->name,
            'functional_code' => $article->functional_code,
            'economic_code' => $article->economic_code,
            'department' => $article->department,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(BudgetArticle::class, [
        'name' => $article->name,
        'functional_code' => $article->functional_code,
    ]);
});

it('can update a budget article', function (): void {
    $article = BudgetArticle::factory()->create();
    $newData = BudgetArticle::factory()->make();

    livewire(EditBudgetArticle::class, ['record' => $article->id])
        ->fillForm([
            'name' => $newData->name,
            'functional_code' => $newData->functional_code,
            'economic_code' => $newData->economic_code,
            'department' => $newData->department,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(BudgetArticle::class, [
        'id' => $article->id,
        'name' => $newData->name,
        'functional_code' => $newData->functional_code,
    ]);
});

it('validates the form data', function (array $data, array $errors): void {
    $article = BudgetArticle::factory()->create();
    $newData = BudgetArticle::factory()->make();

    livewire(EditBudgetArticle::class, ['record' => $article->id])
        ->fillForm([
            'name' => $newData->name,
            'functional_code' => $newData->functional_code,
            'economic_code' => $newData->economic_code,
            'department' => $newData->department,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`functional_code` is required' => [['functional_code' => null], ['functional_code' => 'required']],
    '`functional_code` is max 255 characters' => [['functional_code' => Str::random(256)], ['functional_code' => 'max']],
    '`economic_code` is required' => [['economic_code' => null], ['economic_code' => 'required']],
    '`economic_code` is max 255 characters' => [['economic_code' => Str::random(256)], ['economic_code' => 'max']],
    '`department` is required' => [['department' => null], ['department' => 'required']],
]);
