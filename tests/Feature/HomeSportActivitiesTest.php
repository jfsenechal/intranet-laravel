<?php

declare(strict_types=1);

use AcMarche\App\Models\Article;

use function Pest\Livewire\livewire;

it('shows the first article title, excerpt and link', function (): void {
    $article = Article::factory()->create([
        'title' => 'Tournoi de football inter-services',
        'excerpt' => 'Inscrivez votre équipe avant le 15 septembre.',
    ]);

    livewire('home.sport-activities')
        ->assertOk()
        ->assertSee($article->title)
        ->assertSee($article->excerpt)
        ->assertSeeHtml('href="/articles/'.$article->id.'"');
});

it('shows only the first article when several exist', function (): void {
    $first = Article::factory()->create(['title' => 'Premier article']);
    $second = Article::factory()->create(['title' => 'Second article']);

    livewire('home.sport-activities')
        ->assertOk()
        ->assertSee($first->title)
        ->assertDontSee($second->title)
        ->assertSeeHtml('href="/articles/'.$first->id.'"');
});

it('falls back to a message when there is no article', function (): void {
    livewire('home.sport-activities')
        ->assertOk()
        ->assertSee('Aucun article disponible.')
        ->assertDontSeeHtml('href="/articles/');
});
