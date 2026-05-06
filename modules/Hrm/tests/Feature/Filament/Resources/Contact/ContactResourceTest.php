<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Contacts\Pages\CreateContact;
use AcMarche\Hrm\Filament\Resources\Contacts\Pages\EditContact;
use AcMarche\Hrm\Filament\Resources\Contacts\Pages\ListContacts;
use AcMarche\Hrm\Models\Contact;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListContacts::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateContact::class)
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Contact::factory()->create();

        Livewire::test(EditContact::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'last_name' => $record->last_name,
                'first_name' => $record->first_name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a contact', function (): void {
        Livewire::test(CreateContact::class)
            ->fillForm([
                'last_name' => 'Doe',
                'first_name' => 'John',
                'email_1' => 'john1@example.com',
                'email_2' => 'john2@example.com',
                'phone_1' => '0123456789',
                'phone_2' => '0987654321',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas(Contact::class, [
            'last_name' => 'Doe',
            'first_name' => 'John',
        ]);
    });

    it('can update a contact', function (): void {
        $record = Contact::factory()->create();

        Livewire::test(EditContact::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'last_name' => 'NewLastName',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas(Contact::class, [
            'id' => $record->id,
            'last_name' => 'NewLastName',
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Contact::factory()->make();

        Livewire::test(CreateContact::class)
            ->fillForm([
                'last_name' => $newData->last_name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`last_name` is required' => [['last_name' => null], ['last_name' => 'required']],
        '`last_name` is max 255 characters' => [['last_name' => Str::random(256)], ['last_name' => 'max']],
        '`email_1` must be a valid email' => [['email_1' => 'not-an-email'], ['email_1' => 'email']],
        '`email_2` must be a valid email' => [['email_2' => 'not-an-email'], ['email_2' => 'email']],
    ]);
});
