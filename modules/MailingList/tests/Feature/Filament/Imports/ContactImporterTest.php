<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Imports\ContactImporter;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ListAddressBooks;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\CreateContact;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\actingAs;
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
        ->and($columns['phone'])->toContain('tél. personnel')
        ->and($columns['mobile'])->toContain('portable')
        ->and($columns['street'])->toContain('adresse privée')
        ->and($columns['postal_code'])->toContain('code postal')
        ->and($columns['city'])->toContain('ville')
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

it('imports a csv with repeated column names by keeping the first one', function () {
    Queue::fake();
    $user = auth()->user();
    $addressBook = AddressBook::factory()->create(['username' => auth()->user()->username]);

    $csvFile = UploadedFile::fake()->createWithContent('contacts.csv', implode("\n", [
        'Prénom,Nom de famille,Nom à afficher,Adresse e-mail principale,Portable,Adresse privée,Ville,Code postal,Ville,Code postal',
        ',,Alain Dantinne,familledantinne@gmail.com,0470 12 34 56,Rue des Écoles 1,Marche,6900,Namur,5000',
    ]));

    livewire(ListAddressBooks::class)
        ->mountAction(TestAction::make('import'))
        ->setActionData(['file' => $csvFile])
        ->assertActionDataSet([
            'columnMap.email' => 'Adresse e-mail principale',
            'columnMap.display_name' => 'Nom à afficher',
            'columnMap.mobile' => 'Portable',
            'columnMap.street' => 'Adresse privée',
            'columnMap.postal_code' => 'Code postal',
            'columnMap.city' => 'Ville',
        ])
        ->setActionData(['address_book_id' => $addressBook->id])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    // The import job logs the user out when it finishes, so log back in afterwards.
    Queue::pushed(ImportCsv::class)->each(fn (ImportCsv $job) => $job->handle());
    actingAs($user);

    $contact = Contact::query()->where('email', 'familledantinne@gmail.com')->sole();

    expect($contact->first_name)->toBe('Alain')
        ->and($contact->last_name)->toBe('Dantinne')
        ->and($contact->mobile)->toBe('0470 12 34 56')
        ->and($contact->street)->toBe('Rue des Écoles 1')
        ->and($contact->postal_code)->toBe('6900')
        ->and($contact->city)->toBe('Marche')
        ->and($contact->addressBooks->pluck('id')->all())->toBe([$addressBook->id]);
});
