<?php

declare(strict_types=1);

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\ContactShare;
use App\Models\User;

beforeEach(function (): void {
    $this->currentUser = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->anotherUser = User::factory()->create();
});

describe('Contact OwnerScope', function (): void {
    it('shows only contacts owned by the current user', function (): void {
        Contact::factory(2)->create(['username' => $this->currentUser->username]);
        Contact::factory(2)->create(['username' => $this->otherUser->username]);

        $this->actingAs($this->currentUser);
        $contacts = Contact::all();

        expect($contacts)->toHaveCount(2);
        expect($contacts->pluck('username')->unique()->all())->toBe([$this->currentUser->username]);
    });

    it('shows contacts shared with the current user', function (): void {
        $ownedContact = Contact::factory()->create(['username' => $this->currentUser->username]);
        $sharedContact = Contact::factory()->create(['username' => $this->otherUser->username]);
        $notSharedContact = Contact::factory()->create(['username' => $this->anotherUser->username]);

        ContactShare::create([
            'contact_id' => $sharedContact->id,
            'username' => $this->currentUser->username,
            'permission' => 'read',
        ]);

        $this->actingAs($this->currentUser);
        $contacts = Contact::all();

        expect($contacts)->toHaveCount(2);
        expect($contacts->pluck('id')->sort()->values()->all())->toBe(
            [$ownedContact->id, $sharedContact->id]
        );
    });

    it('does not show contacts not owned or shared with the user', function (): void {
        Contact::factory()->create(['username' => $this->otherUser->username]);
        Contact::factory()->create(['username' => $this->anotherUser->username]);

        $this->actingAs($this->currentUser);
        $contacts = Contact::all();

        expect($contacts)->toHaveCount(0);
    });

    it('shows multiple shared contacts', function (): void {
        $ownedContact = Contact::factory()->create(['username' => $this->currentUser->username]);
        $sharedContact1 = Contact::factory()->create(['username' => $this->otherUser->username]);
        $sharedContact2 = Contact::factory()->create(['username' => $this->otherUser->username]);

        ContactShare::create([
            'contact_id' => $sharedContact1->id,
            'username' => $this->currentUser->username,
            'permission' => 'read',
        ]);

        ContactShare::create([
            'contact_id' => $sharedContact2->id,
            'username' => $this->currentUser->username,
            'permission' => 'write',
        ]);

        $this->actingAs($this->currentUser);
        $contacts = Contact::all();

        expect($contacts)->toHaveCount(3);
    });
});

describe('AddressBook OwnerScope', function (): void {
    it('shows only address books owned by the current user', function (): void {
        AddressBook::factory(2)->create(['username' => $this->currentUser->username]);
        AddressBook::factory(2)->create(['username' => $this->otherUser->username]);

        $this->actingAs($this->currentUser);
        $addressBooks = AddressBook::all();

        expect($addressBooks)->toHaveCount(2);
        expect($addressBooks->pluck('username')->unique()->all())->toBe([$this->currentUser->username]);
    });

    it('shows address books shared with the current user', function (): void {
        $ownedBook = AddressBook::factory()->create(['username' => $this->currentUser->username]);
        $sharedBook = AddressBook::factory()->create(['username' => $this->otherUser->username]);
        $notSharedBook = AddressBook::factory()->create(['username' => $this->anotherUser->username]);

        AddressBookShare::create([
            'address_book_id' => $sharedBook->id,
            'username' => $this->currentUser->username,
            'permission' => 'read',
        ]);

        $this->actingAs($this->currentUser);
        $addressBooks = AddressBook::all();

        expect($addressBooks)->toHaveCount(2);
        expect($addressBooks->pluck('id')->sort()->values()->all())->toBe(
            [$ownedBook->id, $sharedBook->id]
        );
    });

    it('does not show address books not owned or shared with the user', function (): void {
        AddressBook::factory()->create(['username' => $this->otherUser->username]);
        AddressBook::factory()->create(['username' => $this->anotherUser->username]);

        $this->actingAs($this->currentUser);
        $addressBooks = AddressBook::all();

        expect($addressBooks)->toHaveCount(0);
    });

    it('shows multiple shared address books', function (): void {
        $ownedBook = AddressBook::factory()->create(['username' => $this->currentUser->username]);
        $sharedBook1 = AddressBook::factory()->create(['username' => $this->otherUser->username]);
        $sharedBook2 = AddressBook::factory()->create(['username' => $this->otherUser->username]);

        AddressBookShare::create([
            'address_book_id' => $sharedBook1->id,
            'username' => $this->currentUser->username,
            'permission' => 'read',
        ]);

        AddressBookShare::create([
            'address_book_id' => $sharedBook2->id,
            'username' => $this->currentUser->username,
            'permission' => 'write',
        ]);

        $this->actingAs($this->currentUser);
        $addressBooks = AddressBook::all();

        expect($addressBooks)->toHaveCount(3);
    });
});
