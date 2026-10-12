<?php

declare(strict_types=1);

use AcMarche\MailingList\Enums\SharePermission;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\EditAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ListAddressBooks;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers\ContactsRelationManager;
use AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers\SharesRelationManager;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\EditEmail;
use AcMarche\MailingList\Handler\MailerHandler;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('lists public address books of other users but not their private ones', function () {
    $publicAddressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);
    $privateAddressBook = AddressBook::factory()->create(['username' => 'someone-else']);

    livewire(ListAddressBooks::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$publicAddressBook])
        ->assertCanNotSeeTableRecords([$privateAddressBook]);
});

it('shows a public address book read-only to other users', function () {
    $addressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->assertOk()
        ->assertActionHidden(TestAction::make('edit'))
        ->assertActionHidden(TestAction::make('delete'));

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->assertForbidden();
});

it('lists the contacts of a public address book without any write action', function () {
    $addressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);
    $contact = Contact::factory()->create(['username' => 'someone-else']);
    $addressBook->contacts()->attach($contact);

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertCanSeeTableRecords([$contact])
        ->assertActionHidden(TestAction::make('addContact')->table())
        ->assertActionHidden(TestAction::make('edit')->table($contact))
        ->assertActionHidden(TestAction::make('detach')->table($contact))
        ->assertActionVisible(TestAction::make('export')->table());
});

it('does not bulk delete a public address book of another user', function () {
    $addressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);

    livewire(ListAddressBooks::class)
        ->loadTable()
        ->selectTableRecords([$addressBook])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(AddressBook::query()->find($addressBook->id))->not->toBeNull();
});

it('lets the owner make an address book public', function () {
    $addressBook = AddressBook::factory()->create(['username' => auth()->user()->username]);

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->fillForm(['is_public' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($addressBook->fresh()->is_public)->toBeTrue();
});

it('sends to every contact of a public address book of another user', function () {
    $publicAddressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);
    $privateAddressBook = AddressBook::factory()->create(['username' => 'someone-else']);
    $publicContact = Contact::factory()->create(['username' => 'someone-else']);
    $privateContact = Contact::factory()->create(['username' => 'someone-else']);
    $publicAddressBook->contacts()->attach($publicContact);
    $privateAddressBook->contacts()->attach($privateContact);

    $email = Email::factory()->create(['username' => auth()->user()->username]);

    MailerHandler::syncRecipients($email, [$publicAddressBook->id, $privateAddressBook->id], []);

    expect($email->recipients()->pluck('email_address')->all())->toBe([$publicContact->email]);
});

it('keeps the public address book selected when editing a draft', function () {
    $addressBook = AddressBook::factory()->public()->create(['username' => 'someone-else']);
    $contact = Contact::factory()->create(['username' => 'someone-else']);
    $addressBook->contacts()->attach($contact);

    $email = Email::factory()->create(['username' => auth()->user()->username]);
    MailerHandler::syncRecipients($email, [$addressBook->id], []);

    livewire(EditEmail::class, ['record' => $email->id])
        ->assertSet('data.address_book_ids', [$addressBook->id]);
});

it('makes a shared address book read-only for the users it is shared with', function () {
    $addressBook = AddressBook::factory()->create(['username' => 'someone-else']);
    AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => auth()->user()->username,
        'permission' => 'read',
    ]);
    $contact = Contact::factory()->create(['username' => 'someone-else']);
    $addressBook->contacts()->attach($contact);

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->assertOk()
        ->assertActionHidden(TestAction::make('edit'))
        ->assertActionHidden(TestAction::make('delete'));

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->assertForbidden();

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertCanSeeTableRecords([$contact])
        ->assertActionHidden(TestAction::make('addContact')->table())
        ->assertActionHidden(TestAction::make('edit')->table($contact))
        ->assertActionHidden(TestAction::make('detach')->table($contact));
});

it('sends to every contact of an address book shared with the user', function () {
    $addressBook = AddressBook::factory()->create(['username' => 'someone-else']);
    AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => auth()->user()->username,
        'permission' => 'read',
    ]);
    $contact = Contact::factory()->create(['username' => 'someone-else']);
    $addressBook->contacts()->attach($contact);

    $email = Email::factory()->create(['username' => auth()->user()->username]);

    MailerHandler::syncRecipients($email, [$addressBook->id], []);

    expect($email->recipients()->pluck('email_address')->all())->toBe([$contact->email])
        ->and(AddressBook::query()->mailableBy(auth()->user())->pluck('id')->all())->toBe([$addressBook->id]);
});

it('lets a write share edit the address book and its contacts but not delete it or manage shares', function () {
    $addressBook = AddressBook::factory()->create(['username' => 'someone-else']);
    $share = AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => auth()->user()->username,
        'permission' => SharePermission::Write,
    ]);
    $contact = Contact::factory()->create(['username' => 'someone-else']);
    $addressBook->contacts()->attach($contact);

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->assertActionVisible(TestAction::make('edit'))
        ->assertActionHidden(TestAction::make('delete'));

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->assertOk()
        ->assertFormFieldDisabled('is_public');

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertCanSeeTableRecords([$contact])
        ->callAction(TestAction::make('edit')->table($contact), data: ['phone' => '0470 12 34 56'])
        ->assertHasNoFormErrors()
        ->assertActionVisible(TestAction::make('detach')->table($contact));

    expect($contact->fresh()->phone)->toBe('0470 12 34 56');

    livewire(SharesRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertActionHidden(TestAction::make('share')->table())
        ->assertActionHidden(TestAction::make('edit')->table($share))
        ->assertActionHidden(TestAction::make('delete')->table($share));
});
