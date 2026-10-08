<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('adds a new contact to the address book from the view page', function () {
    $addressBook = AddressBook::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->callAction(TestAction::make('addContact'), data: [
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

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->callAction(TestAction::make('addContact'), data: [
            'email' => 'marie.dupont@marche.be',
        ])
        ->assertHasFormErrors(['email' => 'unique']);

    expect($addressBook->contacts()->count())->toBe(0);
});
