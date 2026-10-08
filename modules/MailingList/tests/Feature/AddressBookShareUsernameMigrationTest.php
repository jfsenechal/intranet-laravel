<?php

declare(strict_types=1);

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

function runAddressBookShareUsernameMigration(): void
{
    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_10_08_150018_convert_address_book_share_user_ids_to_usernames.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

function shareAddressBookWith(AddressBook $addressBook, string $username): AddressBookShare
{
    return AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => $username,
        'permission' => 'read',
    ]);
}

beforeEach(function () {
    $this->addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
});

it('replaces a user id stored as username by the real username', function () {
    $colleague = User::factory()->create();
    $share = shareAddressBookWith($this->addressBook, (string) $colleague->id);

    runAddressBookShareUsernameMigration();

    expect($share->fresh()->username)->toBe($colleague->username);
});

it('drops an id share that duplicates an existing username share', function () {
    $colleague = User::factory()->create();
    $correctShare = shareAddressBookWith($this->addressBook, $colleague->username);
    $idShare = shareAddressBookWith($this->addressBook, (string) $colleague->id);

    runAddressBookShareUsernameMigration();

    expect($idShare->fresh())->toBeNull()
        ->and($correctShare->fresh()->username)->toBe($colleague->username);
});

it('leaves usernames and unknown ids untouched', function () {
    $colleague = User::factory()->create();
    $usernameShare = shareAddressBookWith($this->addressBook, $colleague->username);
    $unknownShare = shareAddressBookWith($this->addressBook, '999999');

    runAddressBookShareUsernameMigration();

    expect($usernameShare->fresh()->username)->toBe($colleague->username)
        ->and($unknownShare->fresh()->username)->toBe('999999');
});

it('keeps a numeric value that is a real username', function () {
    $numericUser = User::factory()->create(['username' => '42']);
    User::factory()->create(['id' => 42]);
    $share = shareAddressBookWith($this->addressBook, '42');

    runAddressBookShareUsernameMigration();

    expect($share->fresh()->username)->toBe($numericUser->username);
});
