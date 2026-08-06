<?php

declare(strict_types=1);

use AcMarche\News\Models\Category;
use AcMarche\News\Models\News;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Mail::fake();
});

it('lets a guest read a news article without authentication', function (): void {
    auth()->logout();

    $news = News::factory()->create(['name' => 'Une actualité publique']);

    $this->assertGuest();

    $this->get(route('news.show', $news))
        ->assertOk()
        ->assertSee('Une actualité publique');
});

it('renders the panel infolist of the article for a guest', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('news/photo.jpg', 'fake');

    $category = Category::factory()->create(['name' => 'Communication']);
    $news = News::factory()->create([
        'name' => 'Une actualité complète',
        'content' => '<p>Le corps de la publication.</p>',
        'category_id' => $category->id,
        'medias' => ['news/photo.jpg'],
    ]);

    auth()->logout();

    $this->get(route('news.show', $news))
        ->assertOk()
        ->assertSee('Une actualité complète')
        ->assertSee('Le corps de la publication.')
        ->assertSee('Communication')
        // Metadata and media gallery entries of NewsInfolist.
        ->assertSee('Pour qui ?', escape: false)
        ->assertSee('Auteur')
        ->assertSee($news->user_add)
        ->assertSee(Storage::disk('public')->url('news/photo.jpg'), escape: false);
});

it('returns 404 for a missing news article', function (): void {
    auth()->logout();

    $this->get(route('news.show', 999999))
        ->assertNotFound();
});
