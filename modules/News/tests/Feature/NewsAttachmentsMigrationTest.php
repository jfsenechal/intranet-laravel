<?php

declare(strict_types=1);

use AcMarche\News\Models\News;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const NEWS_LEGACY_ATTACHMENT_COLUMNS = ['attach1Name', 'attach2Name', 'attach3Name'];

/**
 * Put the legacy attachment columns back so the migration has something to
 * move. The fresh-install schema never creates them.
 */
function restoreNewsLegacyColumns(): void
{
    Schema::connection('maria-news')->table('news', function (Blueprint $table): void {
        foreach (NEWS_LEGACY_ATTACHMENT_COLUMNS as $column) {
            $table->string($column)->nullable();
        }
    });
}

/**
 * @param  array<string, string|null>  $attachments
 */
function legacyNewsWithAttachments(array $attachments, ?string $medias = null): News
{
    $news = News::factory()->create();

    DB::connection('maria-news')
        ->table('news')
        ->where('id', $news->getKey())
        ->update([...$attachments, 'medias' => $medias]);

    return $news;
}

function runNewsAttachmentsMigration(): void
{
    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_07_28_090220_move_news_attachments_to_medias.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

beforeEach(function (): void {
    restoreNewsLegacyColumns();
});

it('moves the legacy attachment names into medias and drops the columns', function (): void {
    $full = legacyNewsWithAttachments([
        'attach1Name' => 'report.pdf',
        'attach2Name' => 'photo.jpg',
        'attach3Name' => 'plan.PNG',
    ]);

    runNewsAttachmentsMigration();

    expect($full->refresh()->medias)->toBe([
        'news/report.pdf',
        'news/photo.jpg',
        'news/plan.PNG',
    ]);

    foreach (NEWS_LEGACY_ATTACHMENT_COLUMNS as $column) {
        expect(Schema::connection('maria-news')->hasColumn('news', $column))->toBeFalse();
    }
});

it('keeps the remaining files in order when an attachment slot is empty', function (): void {
    $gap = legacyNewsWithAttachments([
        'attach1Name' => null,
        'attach2Name' => 'second.pdf',
        'attach3Name' => '   ',
    ]);

    runNewsAttachmentsMigration();

    expect($gap->refresh()->medias)->toBe(['news/second.pdf']);
});

it('leaves a news without attachments alone', function (): void {
    $empty = legacyNewsWithAttachments([
        'attach1Name' => null,
        'attach2Name' => null,
        'attach3Name' => null,
    ]);

    runNewsAttachmentsMigration();

    expect($empty->refresh()->medias)->toBeNull();
});

it('does not overwrite medias that are already set', function (): void {
    $uploaded = legacyNewsWithAttachments(
        ['attach1Name' => 'legacy.pdf', 'attach2Name' => null, 'attach3Name' => null],
        json_encode(['news/01KX2WNA2N3067D1H1CN5NSMJ0.png']),
    );

    runNewsAttachmentsMigration();

    expect($uploaded->refresh()->medias)->toBe(['news/01KX2WNA2N3067D1H1CN5NSMJ0.png']);
});

it('is a no-op when the legacy columns are already gone', function (): void {
    $news = legacyNewsWithAttachments(['attach1Name' => 'report.pdf']);

    runNewsAttachmentsMigration();
    runNewsAttachmentsMigration();

    expect($news->refresh()->medias)->toBe(['news/report.pdf']);
});
