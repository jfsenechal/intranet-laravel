<?php

declare(strict_types=1);

use AcMarche\News\Enums\DepartmentEnum;
use AcMarche\News\Events\NewsProcessed;
use AcMarche\News\Listeners\NewsNotification;
use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

function notifyNews(News $news): void
{
    (new NewsNotification())->handle(new NewsProcessed($news));
}

it('registers the news notification listener for the news processed event', function (): void {
    expect(Event::getListeners(NewsProcessed::class))->not->toBeEmpty();
});

it('builds the mail attachments when the news has no medias', function (): void {
    $news = News::factory()->create(['medias' => null]);

    expect((new NewsEmail($news))->attachments())->toBeArray()->toBeEmpty();
});

it('sends a common news notification to every user with an email', function (): void {
    $recipients = User::factory()->count(3)->create();

    $news = News::factory()->create(['department' => DepartmentEnum::COMMON->value]);

    notifyNews($news);

    $expectedCount = User::query()->whereNotNull('email')->count();
    Mail::assertQueued(NewsEmail::class, $expectedCount);

    foreach ($recipients as $recipient) {
        Mail::assertQueued(
            NewsEmail::class,
            fn (NewsEmail $mail): bool => $mail->hasTo($recipient->email) && $mail->news->is($news),
        );
    }
});

it('sends a department news notification only to users of that department', function (): void {
    $cpasUser = User::factory()->create(['departments' => [DepartmentEnum::CPAS->value]]);
    $villeUser = User::factory()->create(['departments' => [DepartmentEnum::VILLE->value]]);

    $news = News::factory()->create(['department' => DepartmentEnum::CPAS->value]);

    notifyNews($news);

    Mail::assertQueued(
        NewsEmail::class,
        fn (NewsEmail $mail): bool => $mail->hasTo($cpasUser->email),
    );

    Mail::assertNotQueued(
        NewsEmail::class,
        fn (NewsEmail $mail): bool => $mail->hasTo($villeUser->email),
    );
});

it('attaches the medias for a user who opted in to attachments', function (): void {
    User::factory()->create([
        'departments' => [DepartmentEnum::COMMON->value],
        'news_attachment' => true,
    ]);

    $news = News::factory()->create(['medias' => ['uploads/news/report.pdf']]);

    notifyNews($news);

    Mail::assertQueued(
        NewsEmail::class,
        fn (NewsEmail $mail): bool => $mail->attachMedias === true
            && $mail->attachments() !== [],
    );
});

it('does not attach the medias for a user who opted out of attachments', function (): void {
    User::factory()->create([
        'departments' => [DepartmentEnum::COMMON->value],
        'news_attachment' => false,
    ]);

    $news = News::factory()->create(['medias' => ['uploads/news/report.pdf']]);

    notifyNews($news);

    Mail::assertQueued(
        NewsEmail::class,
        fn (NewsEmail $mail): bool => $mail->attachMedias === false
            && $mail->attachments() === [],
    );
});

it('shows the intranet notice when the medias are not attached', function (): void {
    $news = News::factory()->create(['medias' => ['uploads/news/report.pdf']]);

    $rendered = (new NewsEmail($news, attachMedias: false))->render();

    expect($rendered)
        ->toContain('pièce(s) jointe(s)')
        ->toContain(route('news.show', $news));
});
