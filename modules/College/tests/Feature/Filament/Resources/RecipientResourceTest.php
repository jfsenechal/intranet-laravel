<?php

declare(strict_types=1);

use AcMarche\College\Enums\RolesEnum;
use AcMarche\College\Filament\Resources\Recipients\Pages\CreateRecipient;
use AcMarche\College\Filament\Resources\Recipients\Pages\EditRecipient;
use AcMarche\College\Filament\Resources\Recipients\Pages\ListRecipients;
use AcMarche\College\Filament\Resources\Recipients\Pages\ViewRecipient;
use AcMarche\College\Models\Recipient;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('college-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->convocation = User::factory()->create();
    $convocationRole = Role::factory()->create(['name' => RolesEnum::ROLE_COLLEGE_CONVOCATION->value]);
    $this->convocation->roles()->attach($convocationRole);

    $this->actingAs($this->admin);
});

it('renders list, create, view and edit pages', function (): void {
    $recipient = Recipient::factory()->create();

    livewire(ListRecipients::class)->assertOk();
    livewire(CreateRecipient::class)->assertOk();
    livewire(ViewRecipient::class, ['record' => $recipient->id])->assertOk();
    livewire(EditRecipient::class, ['record' => $recipient->id])->assertOk();
});

it('lists recipients', function (): void {
    $recipients = Recipient::factory(3)->create();

    livewire(ListRecipients::class)
        ->loadTable()
        ->assertCanSeeTableRecords($recipients);
});

it('creates a recipient via the form', function (): void {
    livewire(CreateRecipient::class)
        ->fillForm([
            'last_name' => 'Dupont',
            'first_name' => 'Jean',
            'email' => 'jean.dupont@example.com',
            'pv_college' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Recipient::class, [
        'last_name' => 'Dupont',
        'first_name' => 'Jean',
        'email' => 'jean.dupont@example.com',
        'pv_college' => true,
    ]);
});

it('auto-generates the slugname if left empty', function (): void {
    livewire(CreateRecipient::class)
        ->fillForm([
            'last_name' => 'Martin',
            'first_name' => 'Marie',
            'email' => 'marie.martin@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Recipient::class, [
        'last_name' => 'Martin',
        'slugname' => 'martin_marie',
    ]);
});

it('updates a recipient via the form', function (): void {
    $recipient = Recipient::factory()->create(['pv_college' => false]);

    livewire(EditRecipient::class, ['record' => $recipient->id])
        ->fillForm(['pv_college' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Recipient::class, [
        'id' => $recipient->id,
        'pv_college' => true,
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateRecipient::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`last_name` required' => [['last_name' => null, 'first_name' => 'X', 'email' => 'x@x.be'], ['last_name' => 'required']],
    '`first_name` required' => [['last_name' => 'X', 'first_name' => null, 'email' => 'x@x.be'], ['first_name' => 'required']],
    '`email` required' => [['last_name' => 'X', 'first_name' => 'Y', 'email' => null], ['email' => 'required']],
    '`email` must be valid' => [['last_name' => 'X', 'first_name' => 'Y', 'email' => 'not-an-email'], ['email' => 'email']],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListRecipients::class)->assertForbidden();
});
