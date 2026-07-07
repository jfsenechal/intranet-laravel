<?php

declare(strict_types=1);

use AcMarche\News\Models\News;
use Illuminate\Support\Facades\Mail;

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

it('returns 404 for a missing news article', function (): void {
    auth()->logout();

    $this->get(route('news.show', 999999))
        ->assertNotFound();
});
