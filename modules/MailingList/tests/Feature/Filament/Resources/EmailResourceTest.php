<?php

declare(strict_types=1);

use AcMarche\MailingList\Enums\EmailStatus;
use AcMarche\MailingList\Enums\RecipientStatus;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\CreateEmail;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\EditEmail;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\ListEmails;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Models\Email;
use AcMarche\MailingList\Models\EmailRecipient;
use AcMarche\MailingList\Models\Sender;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list'));
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->sender = Sender::factory()->create(['username' => $this->user->username]);
});

it('can render the index page', function (): void {
    livewire(ListEmails::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateEmail::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
    ]);

    livewire(EditEmail::class, ['record' => $email->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'subject' => $email->subject,
        ]);
});

it('can create an email with contacts', function (): void {
    $contacts = Contact::factory(3)->create(['username' => $this->user->username]);

    livewire(CreateEmail::class)
        ->fillForm([
            'sender_id' => $this->sender->id,
            'subject' => 'Test Newsletter',
            'body' => '<p>Hello World</p>',
            'contact_ids' => $contacts->pluck('id')->all(),
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Email::class, [
        'subject' => 'Test Newsletter',
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
        'total_count' => 3,
    ]);

    expect(EmailRecipient::count())->toBe(3);
});

it('can create an email with address book', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);
    $contacts = Contact::factory(5)->create(['username' => $this->user->username]);
    $addressBook->contacts()->attach($contacts->pluck('id'));

    livewire(CreateEmail::class)
        ->fillForm([
            'sender_id' => $this->sender->id,
            'subject' => 'Address Book Newsletter',
            'body' => '<p>Content</p>',
            'address_book_ids' => [$addressBook->id],
        ])
        ->call('create')
        ->assertNotified();

    $email = Email::query()->where('subject', 'Address Book Newsletter')->first();
    expect($email->total_count)->toBe(5);
    expect($email->recipients)->toHaveCount(5);
});

it('deduplicates contacts from multiple sources', function (): void {
    $addressBook = AddressBook::factory()->create(['username' => $this->user->username]);
    $contacts = Contact::factory(3)->create(['username' => $this->user->username]);
    $addressBook->contacts()->attach($contacts->pluck('id'));

    livewire(CreateEmail::class)
        ->fillForm([
            'sender_id' => $this->sender->id,
            'subject' => 'Dedup Test',
            'body' => '<p>Content</p>',
            'address_book_ids' => [$addressBook->id],
            'contact_ids' => [$contacts->first()->id],
        ])
        ->call('create')
        ->assertNotified();

    $email = Email::query()->where('subject', 'Dedup Test')->first();
    expect($email->total_count)->toBe(3);
});

it('can update an email', function (): void {
    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
    ]);

    livewire(EditEmail::class, ['record' => $email->id])
        ->fillForm([
            'subject' => 'Updated Subject',
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Email::class, [
        'id' => $email->id,
        'subject' => 'Updated Subject',
    ]);
});

it('can delete an email', function (): void {
    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
    ]);

    livewire(EditEmail::class, ['record' => $email->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($email);
});

it('can dispatch send action', function (): void {
    Bus::fake();

    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
        'status' => EmailStatus::Draft,
        'total_count' => 2,
    ]);

    $contacts = Contact::factory(2)->create(['username' => $this->user->username]);
    foreach ($contacts as $contact) {
        EmailRecipient::factory()->create([
            'email_id' => $email->id,
            'contact_id' => $contact->id,
            'email_address' => $contact->email,
            'status' => RecipientStatus::Pending,
        ]);
    }

    livewire(EditEmail::class, ['record' => $email->id])
        ->callAction('send')
        ->assertNotified();

    Bus::assertBatched(fn ($batch): bool => $batch->jobs->count() === 2);

    $email->refresh();
    expect($email->status)->toBe(EmailStatus::Sending);
    expect($email->batch_id)->not->toBeNull();
});

it('prevents sending with no recipients', function (): void {
    $email = Email::factory()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
        'status' => EmailStatus::Draft,
        'total_count' => 0,
    ]);

    livewire(EditEmail::class, ['record' => $email->id])
        ->callAction('send')
        ->assertNotified();

    $email->refresh();
    expect($email->status)->toBe(EmailStatus::Draft);
});

it('hides send action for sent emails', function (): void {
    $email = Email::factory()->sent()->create([
        'username' => $this->user->username,
        'sender_id' => $this->sender->id,
    ]);

    livewire(EditEmail::class, ['record' => $email->id])
        ->assertActionHidden('send');
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateEmail::class)
        ->fillForm([
            'sender_id' => $this->sender->id,
            'subject' => 'Test',
            'body' => '<p>Content</p>',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`subject` is required' => [['subject' => null], ['subject' => 'required']],
    '`sender_id` is required' => [['sender_id' => null], ['sender_id' => 'required']],
]);

it('has table columns', function (string $column): void {
    livewire(ListEmails::class)
        ->assertTableColumnExists($column);
})->with(['subject', 'sender.name', 'status', 'created_at', 'updated_at']);
