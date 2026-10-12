<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Imports\ContactImporter;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\CreateContact;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('guesses the columns of a Thunderbird export', function () {
    $columns = collect(ContactImporter::getColumns())
        ->mapWithKeys(fn ($column): array => [$column->getName() => $column->getGuesses()]);

    expect($columns['email'])->toContain('adresse e-mail principale')
        ->and($columns['last_name'])->toContain('nom de famille')
        ->and($columns['display_name'])->toContain('nom à afficher')
        ->and($columns['phone'])->toContain('portable')
        ->and($columns['description'])->toContain('notes');
});

it('splits the display name when first and last name are empty', function () {
    $addressBook = AddressBook::factory()->create(['username' => auth()->user()->username]);

    ContactImporter::test(options: ['address_book_id' => $addressBook->id])
        ->import([
            'last_name' => '',
            'first_name' => '',
            'display_name' => 'Alain Dantinne',
            'email' => 'familledantinne@gmail.com',
        ])
        ->assertImported();

    $contact = Contact::query()->where('email', 'familledantinne@gmail.com')->sole();

    expect($contact->first_name)->toBe('Alain')
        ->and($contact->last_name)->toBe('Dantinne')
        ->and($contact->username)->toBe(auth()->user()->username)
        ->and($contact->addressBooks->pluck('id')->all())->toBe([$addressBook->id]);
});

it('keeps explicit first and last names over the display name', function () {
    ContactImporter::test()
        ->import([
            'last_name' => 'Martin',
            'first_name' => 'Mélodie',
            'display_name' => 'Mel',
            'email' => 'melodie.martin@marche.be',
        ])
        ->assertImported();

    $contact = Contact::query()->where('email', 'melodie.martin@marche.be')->sole();

    expect($contact->first_name)->toBe('Mélodie')
        ->and($contact->last_name)->toBe('Martin');
});

it('creates its own contact when another user already has the email', function () {
    $otherContact = Contact::factory()->create([
        'username' => 'someone-else',
        'email' => 'vincent.bernard@ac.marche.be',
    ]);

    ContactImporter::test()
        ->import(['email' => 'vincent.bernard@ac.marche.be'])
        ->assertImported();

    expect(Contact::withoutGlobalScopes()->where('email', 'vincent.bernard@ac.marche.be')->count())->toBe(2)
        ->and($otherContact->fresh()->username)->toBe('someone-else');
});

it('updates the existing contact of the current user', function () {
    $contact = Contact::factory()->create([
        'username' => auth()->user()->username,
        'email' => 'vincent.bernard@ac.marche.be',
    ]);

    ContactImporter::test()
        ->import(['email' => 'vincent.bernard@ac.marche.be', 'phone' => '0470 12 34 56'])
        ->assertImported();

    expect(Contact::withoutGlobalScopes()->where('email', 'vincent.bernard@ac.marche.be')->count())->toBe(1)
        ->and($contact->fresh()->phone)->toBe('0470 12 34 56');
});

it('allows creating a contact whose email belongs to another user', function () {
    Contact::factory()->create([
        'username' => 'someone-else',
        'email' => 'marie.dupont@marche.be',
    ]);

    livewire(CreateContact::class)
        ->fillForm(['email' => 'marie.dupont@marche.be'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Contact::withoutGlobalScopes()->where('email', 'marie.dupont@marche.be')->count())->toBe(2);
});
