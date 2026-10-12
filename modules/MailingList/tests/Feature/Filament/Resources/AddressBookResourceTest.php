<?php

declare(strict_types=1);

use AcMarche\MailingList\Enums\SharePermission;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\EditAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers\ContactsRelationManager;
use AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers\SharesRelationManager;
use AcMarche\MailingList\Filament\Resources\Contacts\ContactResource;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\ViewContact;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use AcMarche\MailingList\Models\Contact;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('adds a new contact from the contacts relation manager', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->callAction(TestAction::make('addContact')->table(), data: [
            'last_name' => 'Dupont',
            'first_name' => 'Marie',
            'email' => 'marie.dupont@marche.be',
        ])
        ->assertHasNoFormErrors()
        ->assertNotified('Contact ajouté');

    $contact = Contact::query()->where('email', 'marie.dupont@marche.be')->sole();

    expect($contact->username)->toBe(auth()->user()->username)
        ->and($contact->addressBooks->pluck('id')->all())->toBe([$addressBook->id]);
});

it('rejects a contact whose email already exists', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
    Contact::factory()->create([
        'username' => auth()->user()->username,
        'email' => 'marie.dupont@marche.be',
    ]);

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->callAction(TestAction::make('addContact')->table(), data: [
            'email' => 'marie.dupont@marche.be',
        ])
        ->assertHasFormErrors(['email' => 'unique']);

    expect($addressBook->contacts()->count())->toBe(0);
});

it('shares an address book from the shares relation manager', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $colleague = User::factory()->create();

    livewire(SharesRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->callAction(TestAction::make('share')->table(), data: [
            'username' => $colleague->username,
            'permission' => SharePermission::Write->value,
        ])
        ->assertHasNoFormErrors()
        ->assertNotified('Carnet partagé');

    assertDatabaseHas(AddressBookShare::class, [
        'permission' => SharePermission::Write->value,
        'address_book_id' => $addressBook->id,
        'username' => $colleague->username,
    ]);
});

it('displays the users the address book is shared with', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $colleague = User::factory()->create();
    AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => $colleague->username,
        'permission' => 'read',
    ]);

    $share = AddressBookShare::query()->where('username', $colleague->username)->sole();

    livewire(SharesRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertCanSeeTableRecords([$share])
        ->assertSee($colleague->last_name)
        ->assertSee($colleague->email);

    expect(SharesRelationManager::canViewForRecord($addressBook, EditAddressBook::class))->toBeFalse();
});

it('lists the contacts of the address book with edit and detach actions', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $contact = Contact::factory()->create(['username' => auth()->user()->username]);
    $otherContact = Contact::factory()->create(['username' => auth()->user()->username]);
    $addressBook->contacts()->attach($contact);

    livewire(ContactsRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->loadTable()
        ->assertCanSeeTableRecords([$contact])
        ->assertCanNotSeeTableRecords([$otherContact])
        ->assertActionVisible(TestAction::make('edit')->table($contact))
        ->assertActionHasUrl(TestAction::make('edit')->table($contact), ContactResource::getUrl('edit', ['record' => $contact]))
        ->callAction(TestAction::make('detach')->table($contact))
        ->assertNotified('Contact retiré du carnet');

    expect($addressBook->contacts()->count())->toBe(0)
        ->and(Contact::query()->find($contact->id))->not->toBeNull();
});

it('shows the contact details on the view page', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
        'name' => 'Voisins',
    ]);
    $contact = Contact::factory()->create([
        'username' => auth()->user()->username,
        'last_name' => 'Dupont',
        'email' => 'marie.dupont@marche.be',
    ]);
    $addressBook->contacts()->attach($contact);

    livewire(ViewContact::class, ['record' => $contact->id])
        ->assertOk()
        ->assertSee('Dupont')
        ->assertSee('marie.dupont@marche.be')
        ->assertSee('Voisins');
});

it('shows the contacts relation manager on the view page only', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);

    expect(ContactsRelationManager::canViewForRecord($addressBook, ViewAddressBook::class))->toBeTrue()
        ->and(ContactsRelationManager::canViewForRecord($addressBook, EditAddressBook::class))->toBeFalse();
});

it('changes the permission of a share and removes it', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $share = AddressBookShare::query()->create([
        'address_book_id' => $addressBook->id,
        'username' => User::factory()->create()->username,
        'permission' => SharePermission::Read,
    ]);

    $sharesManager = livewire(SharesRelationManager::class, ['ownerRecord' => $addressBook, 'pageClass' => ViewAddressBook::class])
        ->callAction(TestAction::make('edit')->table($share), data: [
            'permission' => SharePermission::Write->value,
        ])
        ->assertHasNoFormErrors();

    expect($share->fresh()->permission)->toBe(SharePermission::Write);

    $sharesManager->callAction(TestAction::make('delete')->table($share));

    expect(AddressBookShare::query()->find($share->id))->toBeNull();
});
