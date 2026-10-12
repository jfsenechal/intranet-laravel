<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

it('lists the public address books of every user with their contact count', function (): void {
    $addressBook = AddressBook::factory()->public()->create([
        'username' => 'someone-else',
        'name' => 'Associations sportives',
    ]);
    $addressBook->contacts()->attach(Contact::factory()->count(2)->create(['username' => 'someone-else']));

    livewire('home.public-address-books')
        ->assertOk()
        ->assertSee('Associations sportives')
        ->assertSee('2 contacts')
        ->assertSeeHtml('href="'.AddressBookResource::getUrl('view', ['record' => $addressBook->id], panel: 'mailing-list-panel').'"');
});

it('does not list private address books', function (): void {
    AddressBook::factory()->create([
        'username' => auth()->user()->username,
        'name' => 'Mon carnet privé',
    ]);

    livewire('home.public-address-books')
        ->assertOk()
        ->assertDontSee('Mon carnet privé')
        ->assertSee('Aucun carnet d\'adresses public.', escape: false);
});

it('renders nothing for guests', function (): void {
    AddressBook::factory()->public()->create(['name' => 'Associations sportives']);
    Auth::forgetUser();

    livewire('home.public-address-books')
        ->assertOk()
        ->assertDontSee('Carnets d\'adresses publics', escape: false)
        ->assertDontSee('Associations sportives');
});
