<?php

declare(strict_types=1);

use AcMarche\App\Filament\Resources\Articles\Pages\CreateArticle;
use AcMarche\App\Filament\Resources\Articles\Pages\EditArticle;
use AcMarche\App\Filament\Resources\Articles\Pages\ListArticles;
use AcMarche\App\Filament\Resources\Articles\Pages\ViewArticle;
use AcMarche\App\Models\Article;
use AcMarche\Security\Enums\RolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach(Role::factory()->create(['name' => RolesEnum::INTRANET_ADMIN->value]));
});

it('lists the articles for an intranet admin', function (): void {
    $this->actingAs($this->admin);
    $articles = Article::factory()->count(3)->create();

    livewire(ListArticles::class)
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords($articles);
});

it('creates an article', function (): void {
    $this->actingAs($this->admin);

    livewire(CreateArticle::class)
        ->fillForm([
            'title' => 'Nouvel horaire',
            'excerpt' => 'Les bureaux ferment à 16h dès septembre.',
            'body' => '<p>Le détail des horaires est disponible en interne.</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Article::class, [
        'title' => 'Nouvel horaire',
        'excerpt' => 'Les bureaux ferment à 16h dès septembre.',
        'body' => '<p>Le détail des horaires est disponible en interne.</p>',
    ]);
});

it('requires a title and a body', function (): void {
    $this->actingAs($this->admin);

    livewire(CreateArticle::class)
        ->fillForm([
            'title' => null,
            'body' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
            'body',
        ])
        ->assertNotNotified();
});

it('views and updates an article', function (): void {
    $this->actingAs($this->admin);
    $article = Article::factory()->create();

    livewire(ViewArticle::class, ['record' => $article->id])->assertOk();

    livewire(EditArticle::class, ['record' => $article->id])
        ->fillForm(['title' => 'Titre corrigé'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Article::class, [
        'id' => $article->id,
        'title' => 'Titre corrigé',
    ]);
});

it('forbids every crud page to a user without the intranet admin role', function (string $page): void {
    $stranger = User::factory()->create(['is_administrator' => false]);
    $this->actingAs($stranger);
    $article = Article::factory()->create();

    $parameters = $page === ListArticles::class || $page === CreateArticle::class
        ? []
        : ['record' => $article->id];

    livewire($page, $parameters)->assertForbidden();
})->with([
    ListArticles::class,
    CreateArticle::class,
    ViewArticle::class,
    EditArticle::class,
]);

it('forbids the list page to a global administrator who lacks the role', function (): void {
    $administrator = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($administrator);

    livewire(ListArticles::class)->assertForbidden();
});
