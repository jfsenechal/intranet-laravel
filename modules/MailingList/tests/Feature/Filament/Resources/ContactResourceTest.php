<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Contacts\Pages\ListContacts;
use AcMarche\MailingList\Models\Contact;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('exports the contacts of the list to xlsx in table order', function () {
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
        ['Martin', 'paul.martin@marche.be'],
        ['Dupont', 'marie.dupont@marche.be'],
    ]);
});
