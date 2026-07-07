<?php

declare(strict_types=1);

use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('builds the mail attachments when the news has no medias', function (): void {
    $news = News::factory()->create(['medias' => null]);

    expect((new NewsEmail($news))->attachments())->toBeArray();
});

it('sends a news notification to each user with an email', function (): void {
    $recipients = User::factory()->count(3)->create();

    $news = News::factory()->create();

    $expectedCount = User::query()->whereNotNull('email')->count();
    Mail::assertSent(NewsEmail::class, $expectedCount);

    foreach ($recipients as $recipient) {
        Mail::assertSent(
            NewsEmail::class,
            fn (NewsEmail $mail): bool => $mail->hasTo($recipient->email) && $mail->news->is($news),
        );
    }
});
