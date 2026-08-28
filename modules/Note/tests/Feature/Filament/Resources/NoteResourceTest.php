<?php

declare(strict_types=1);

use AcMarche\Note\Filament\Resources\Notes\Pages\CreateNote;
use AcMarche\Note\Filament\Resources\Notes\Pages\EditNote;
use AcMarche\Note\Filament\Resources\Notes\Pages\ListNotes;
use AcMarche\Note\Filament\Resources\Notes\Pages\ViewNote;
use AcMarche\Note\Models\Note;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('note-panel'));

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.note-panel.resources.notes.index')) {
        Route::get('/notes', fn (): string => '')->name('filament.note-panel.resources.notes.index');
        Route::get('/notes/create', fn (): string => '')->name('filament.note-panel.resources.notes.create');
        Route::get('/notes/{record}/edit', fn (): string => '')->name('filament.note-panel.resources.notes.edit');
        Route::get('/notes/{record}', fn (): string => '')->name('filament.note-panel.resources.notes.view');
    }
});

it('can render the index page', function (): void {
    livewire(ListNotes::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateNote::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $note = Note::factory()->create();

    livewire(EditNote::class, [
        'record' => $note->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $note->name,
            'content' => $note->content,
        ]);
});

it('can render the view page', function (): void {
    $note = Note::factory()->create();

    livewire(ViewNote::class, [
        'record' => $note->id,
    ])
        ->assertOk();
});

it('has column', function (string $column): void {
    livewire(ListNotes::class)
        ->assertTableColumnExists($column);
})->with(['name', 'is_encrypted', 'user_add', 'created_at', 'updated_at']);

// user_add and updated_at are toggleable(isToggledHiddenByDefault: true), so they are
// not rendered until the user enables them; only the visible columns are asserted here.
it('can render column', function (string $column): void {
    Note::factory()->create();

    livewire(ListNotes::class)
        ->loadTable()
        ->assertCanRenderTableColumn($column);
})->with(['name', 'is_encrypted', 'created_at']);

it('can load the create form', function (): void {
    livewire(CreateNote::class)
        ->assertSchemaComponentExists('name')
        ->assertSchemaComponentExists('is_encrypted')
        ->assertSchemaComponentExists('content');
});

it('stores the content encrypted when the checkbox is ticked', function (): void {
    livewire(CreateNote::class)
        ->fillForm([
            'name' => 'Note chiffrée',
            'is_encrypted' => true,
            'content' => '<p>Secret</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $note = Note::query()->where('name', 'Note chiffrée')->sole();

    expect($note->is_encrypted)->toBeTrue()
        ->and($note->content)->toBe('<p>Secret</p>')
        ->and($note->getRawOriginal('content'))->not->toContain('Secret');
});

it('leaves the content in plaintext when the checkbox is left unticked', function (): void {
    livewire(CreateNote::class)
        ->fillForm([
            'name' => 'Note claire',
            'is_encrypted' => false,
            'content' => '<p>Visible</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $note = Note::query()->where('name', 'Note claire')->sole();

    expect($note->is_encrypted)->toBeFalse()
        ->and($note->getRawOriginal('content'))->toBe('<p>Visible</p>');
});

it('shows an encrypted note decrypted in the edit form', function (): void {
    $note = Note::factory()->encrypted()->create(['content' => '<p>Secret</p>']);

    livewire(EditNote::class, ['record' => $note->id])
        ->assertSchemaStateSet([
            'content' => '<p>Secret</p>',
            'is_encrypted' => true,
        ]);
});

it('can turn encryption on from the edit form', function (): void {
    $note = Note::factory()->create(['content' => '<p>Secret</p>']);

    livewire(EditNote::class, ['record' => $note->id])
        ->fillForm(['is_encrypted' => true])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($note->fresh()->content)->toBe('<p>Secret</p>')
        ->and($note->fresh()->getRawOriginal('content'))->not->toContain('Secret');
});

it('can create a note and sets user_add to the authenticated user', function (): void {
    $noteData = Note::factory()->make();

    livewire(CreateNote::class)
        ->fillForm([
            'name' => $noteData->name,
            'content' => $noteData->content,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $note = Note::query()->where('name', $noteData->name)->first();

    expect($note)->not->toBeNull()
        ->and($note->user_add)->toBe(auth()->user()->username);
});

it('can delete a note', function (): void {
    $note = Note::factory()->create();

    livewire(ViewNote::class, [
        'record' => $note->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing(Note::class, ['id' => $note->id]);
});

it('can bulk delete notes', function (): void {
    $notes = Note::factory(5)->create();

    livewire(ListNotes::class)
        ->loadTable()
        ->assertCanSeeTableRecords($notes)
        ->selectTableRecords($notes)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($notes);

    $notes->each(fn (Note $note) => assertDatabaseMissing(Note::class, ['id' => $note->id]));
});

it('can search notes by name', function (): void {
    $note1 = Note::factory()->create(['name' => 'Lorem ipsum dolor']);
    $note2 = Note::factory()->create(['name' => 'Consectetur adipiscing']);

    livewire(ListNotes::class)
        ->loadTable()
        ->searchTable('Lorem')
        ->assertCanSeeTableRecords([$note1])
        ->assertCanNotSeeTableRecords([$note2]);
});

it('displays table actions on list page', function (): void {
    $note = Note::factory()->create();

    livewire(ListNotes::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$note])
        ->assertTableActionExists('view');
});

it('displays delete action on view page', function (): void {
    $note = Note::factory()->create();

    livewire(ViewNote::class, [
        'record' => $note->id,
    ])
        ->assertActionExists('delete');
});

/**
 * Create a note authored by somebody other than the current user, then restore the
 * original authentication. HasUserAdd fills `user_add` from the authenticated user,
 * so authorship is established by acting as that other user.
 */
function noteAuthoredBySomebodyElse(): Note
{
    $current = auth()->user();

    test()->actingAs(User::factory()->create(['username' => 'somebody-else']));
    $note = Note::factory()->create();

    test()->actingAs($current);

    return $note;
}

it('only lists notes authored by the current user', function (): void {
    $mine = Note::factory()->create();
    $theirs = noteAuthoredBySomebodyElse();

    livewire(ListNotes::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$theirs]);
});

// Route model binding resolves through NoteResource::getEloquentQuery(), so another
// user's note is simply not found. Over HTTP that surfaces as a 404; in a Livewire
// unit test the exception propagates instead of becoming a response.
it('cannot view a note authored by somebody else', function (): void {
    $theirs = noteAuthoredBySomebodyElse();

    livewire(ViewNote::class, ['record' => $theirs->id]);
})->throws(ModelNotFoundException::class);

it('cannot edit a note authored by somebody else', function (): void {
    $theirs = noteAuthoredBySomebodyElse();

    livewire(EditNote::class, ['record' => $theirs->id]);
})->throws(ModelNotFoundException::class);

it('lets a plain authenticated user list and create notes', function (): void {
    auth()->user()->update(['is_administrator' => false]);

    livewire(ListNotes::class)
        ->assertOk();

    livewire(CreateNote::class)
        ->fillForm([
            'name' => 'Note ouverte',
            'content' => '<p>Contenu</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();
});

it('validates the form data', function (array $data, array $errors): void {
    $noteData = Note::factory()->make();

    livewire(CreateNote::class)
        ->fillForm([
            'name' => $noteData->name,
            'content' => $noteData->content,
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    // A required RichEditor does not report the `required` rule name to Livewire,
    // so the bare key is asserted instead. See .ai/rules/tests.md.
    '`content` is required' => [['content' => null], ['content']],
]);
