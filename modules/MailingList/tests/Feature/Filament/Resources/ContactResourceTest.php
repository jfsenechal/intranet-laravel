<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Contacts\Pages\EditContact;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\ListContacts;
use AcMarche\MailingList\Models\Contact;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('exports the contacts of the list to xlsx sorted by last name', function () {
    Contact::factory()->create([
        'username' => auth()->user()->username,
        'last_name' => 'Martin',
        'email' => 'paul.martin@marche.be',
    ]);
    Contact::factory()->create([
        'username' => auth()->user()->username,
        'last_name' => 'Dupont',
        'email' => 'marie.dupont@marche.be',
    ]);
    Contact::factory()->create([
        'username' => 'someone-else',
        'last_name' => 'Etranger',
        'email' => 'etranger@marche.be',
    ]);

    $component = livewire(ListContacts::class)
        ->callAction('export', data: [
            'columns' => ['last_name', 'email'],
        ])
        ->assertHasNoFormErrors();

    expect(xlsxRows(base64_decode(data_get($component->effects, 'download.content'))))->toBe([
        ['Nom', 'Email'],
        ['Dupont', 'marie.dupont@marche.be'],
        ['Martin', 'paul.martin@marche.be'],
    ]);
});

it('exports the address and mobile columns', function () {
    Contact::factory()->create([
        'username' => auth()->user()->username,
        'last_name' => 'Dupont',
        'mobile' => '0470 12 34 56',
        'street' => 'Rue des Écoles 1',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
    ]);

    $component = livewire(ListContacts::class)
        ->callAction('export', data: [
            'columns' => ['last_name', 'mobile', 'street', 'postal_code', 'city'],
        ])
        ->assertHasNoFormErrors();

    expect(xlsxRows(base64_decode(data_get($component->effects, 'download.content'))))->toBe([
        ['Nom', 'GSM', 'Rue', 'Code postal', 'Localité'],
        ['Dupont', '0470 12 34 56', 'Rue des Écoles 1', '6900', 'Marche-en-Famenne'],
    ]);
});

it('saves the address and mobile from the contact form', function () {
    $contact = Contact::factory()->create(['username' => auth()->user()->username]);

    livewire(EditContact::class, ['record' => $contact->id])
        ->fillForm([
            'mobile' => '0470 12 34 56',
            'street' => 'Rue des Écoles 1',
            'postal_code' => '6900',
            'city' => 'Marche-en-Famenne',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($contact->fresh()->only(['mobile', 'street', 'postal_code', 'city']))->toBe([
        'mobile' => '0470 12 34 56',
        'street' => 'Rue des Écoles 1',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
    ]);
});

it('sorts the contacts by last name by default', function () {
    $martin = Contact::factory()->create(['username' => auth()->user()->username, 'last_name' => 'Martin']);
    $dupont = Contact::factory()->create(['username' => auth()->user()->username, 'last_name' => 'Dupont']);

    livewire(ListContacts::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$dupont, $martin], inOrder: true);
});
