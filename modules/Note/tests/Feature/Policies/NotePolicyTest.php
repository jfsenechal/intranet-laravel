<?php

declare(strict_types=1);

use AcMarche\Note\Models\Note;
use AcMarche\Note\Policies\NotePolicy;
use App\Models\User;

beforeEach(function (): void {
    $this->policy = new NotePolicy;
});

/**
 * Create a note authored by the given username.
 *
 * HasUserAdd fills `user_add` from the authenticated user on create and ignores any
 * value passed to the factory, so the author is established by acting as them.
 */
function noteAuthoredBy(string $username): Note
{
    $previous = auth()->user();

    $author = User::factory()->create(['username' => $username]);
    test()->actingAs($author);

    $note = Note::factory()->create();

    if ($previous !== null) {
        test()->actingAs($previous);
    }

    return $note;
}

it('allows the author to view, update and delete their own note', function (string $ability): void {
    $note = noteAuthoredBy('alice');
    $author = User::query()->where('username', 'alice')->sole();

    expect($note->user_add)->toBe('alice')
        ->and($this->policy->{$ability}($author, $note))->toBeTrue();
})->with(['view', 'update', 'delete']);

it('denies view, update and delete on a note owned by somebody else', function (string $ability): void {
    $note = noteAuthoredBy('bob');
    $intruder = User::factory()->create(['username' => 'alice']);

    expect($this->policy->{$ability}($intruder, $note))->toBeFalse();
})->with(['view', 'update', 'delete']);

it('does not exempt administrators from ownership', function (string $ability): void {
    $note = noteAuthoredBy('bob');
    $admin = User::factory()->create(['username' => 'alice', 'is_administrator' => true]);

    expect($this->policy->{$ability}($admin, $note))->toBeFalse();
})->with(['view', 'update', 'delete']);

it('lets any authenticated user reach the list and create a note', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny())->toBeTrue()
        ->and($this->policy->create($user))->toBeTrue();
});

it('never allows restore or force delete', function (): void {
    expect($this->policy->restore())->toBeFalse()
        ->and($this->policy->forceDelete())->toBeFalse();
});
