<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\CreateAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\EditAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ListAddressBooks;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use AcMarche\MailingList\Models\Contact;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list'));
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the index page', function (): void {
    livewire(ListAddressBooks::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateAddressBook::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $addressBook->name,
        ]);
});

it('can render the view page', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->assertOk();
});

it('can list address books', function (): void {
    $addressBooks = AddressBook::factory(3)->create(['username' => $this->user->username]);

    livewire(ListAddressBooks::class)
        ->loadTable()
        ->assertCanSeeTableRecords($addressBooks);
});

it('has table columns', function (string $column): void {
    livewire(ListAddressBooks::class)
        ->assertTableColumnExists($column);
})->with(['name', 'created_at', 'updated_at']);

it('can sort by name', function (): void {
    $addressBooks = AddressBook::factory(5)->create(['username' => $this->user->username]);

    livewire(ListAddressBooks::class)
        ->loadTable()
        ->sortTable('name')
        ->assertCanSeeTableRecords($addressBooks->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($addressBooks->sortByDesc('name'), inOrder: true);
});

it('can search address books', function (): void {
    $addressBooks = AddressBook::factory(5)->create(['username' => $this->user->username]);

    $search = $addressBooks->first()->name;

    livewire(ListAddressBooks::class)
        ->loadTable()
        ->searchTable($search)
        ->assertCanSeeTableRecords($addressBooks->where('name', $search))
        ->assertCanNotSeeTableRecords($addressBooks->where('name', '!=', $search));
});

it('can create an address book', function (): void {
    livewire(CreateAddressBook::class)
        ->fillForm([
            'name' => 'My Address Book',
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(AddressBook::class, [
        'name' => 'My Address Book',
        'username' => $this->user->username,
    ]);
});

it('can create an address book with contacts', function (): void {
    $contacts = Contact::factory(3)->create(['username' => $this->user->username]);

    livewire(CreateAddressBook::class)
        ->fillForm([
            'name' => 'With Contacts',
            'contacts' => $contacts->pluck('id')->all(),
        ])
        ->call('create')
        ->assertNotified();

    $addressBook = AddressBook::query()->where('name', 'With Contacts')->first();
    expect($addressBook->contacts)->toHaveCount(3);
});

it('can create an address book with shared users', function (): void {
    $otherUser = User::factory()->create();

    livewire(CreateAddressBook::class)
        ->fillForm([
            'name' => 'Shared Book',
            'sharedWithUsers' => [$otherUser->username],
        ])
        ->call('create')
        ->assertNotified();

    $addressBook = AddressBook::query()->where('name', 'Shared Book')->first();

    expect(AddressBookShare::query()
        ->where('address_book_id', $addressBook->id)
        ->where('username', $otherUser->username)
        ->exists()
    )->toBeTrue();
});

it('can update an address book', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(AddressBook::class, [
        'id' => $addressBook->id,
        'name' => 'Updated Name',
    ]);
});

it('can view address book with contacts', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);
    $contacts = Contact::factory(2)->create(['username' => $this->user->username]);
    $addressBook->contacts()->attach($contacts->pluck('id'));

    livewire(ViewAddressBook::class, ['record' => $addressBook->id])
        ->assertOk();
});

it('validates the form data', function (array $data, array $errors): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);

    livewire(EditAddressBook::class, ['record' => $addressBook->id])
        ->fillForm([
            'name' => 'Valid Name',
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
]);
