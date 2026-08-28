<?php

declare(strict_types=1);

use AcMarche\Note\Models\Note;
use Illuminate\Support\Facades\DB;

/**
 * Read the column as stored, bypassing the model's accessor.
 */
function storedContent(Note $note): ?string
{
    return DB::connection('maria-note')
        ->table('notes')
        ->where('id', $note->id)
        ->value('content');
}

it('stores the content as plaintext when encryption is off', function (): void {
    $note = Note::factory()->create(['content' => '<p>Secret</p>']);

    expect(storedContent($note))->toBe('<p>Secret</p>')
        ->and($note->fresh()->content)->toBe('<p>Secret</p>');
});

it('stores the content encrypted when encryption is on', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Secret</p>']);

    expect(storedContent($note))->not->toBe('<p>Secret</p>')
        ->and(storedContent($note))->not->toContain('Secret');
});

it('reads an encrypted note back as plaintext', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Secret</p>']);

    expect($note->fresh()->content)->toBe('<p>Secret</p>');
});

it('encrypts an existing note when the flag is turned on without touching the body', function (): void {
    $note = Note::factory()->create(['content' => '<p>Secret</p>']);

    $note->update(['is_encrypted' => true]);

    expect(storedContent($note))->not->toContain('Secret')
        ->and($note->fresh()->content)->toBe('<p>Secret</p>');
});

it('decrypts an existing note back to plaintext when the flag is turned off', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Secret</p>']);

    $note->update(['is_encrypted' => false]);

    expect(storedContent($note))->toBe('<p>Secret</p>')
        ->and($note->fresh()->content)->toBe('<p>Secret</p>');
});

it('does not double encrypt when an encrypted note is saved untouched', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Secret</p>']);

    $note->fresh()->touch();

    expect($note->fresh()->content)->toBe('<p>Secret</p>');
});

it('re-encrypts when the body of an encrypted note is edited', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Old</p>']);

    $reloaded = $note->fresh();
    $reloaded->update(['content' => '<p>New</p>']);

    expect(storedContent($note))->not->toContain('New')
        ->and($note->fresh()->content)->toBe('<p>New</p>');
});

it('reads back an unsaved encrypted note without trying to decrypt plaintext', function (): void {
    $note = new Note(['name' => 'Draft', 'content' => '<p>Draft body</p>', 'is_encrypted' => true]);

    expect($note->content)->toBe('<p>Draft body</p>');
});
