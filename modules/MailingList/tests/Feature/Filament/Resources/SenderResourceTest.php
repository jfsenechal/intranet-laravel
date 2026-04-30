<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Senders\Pages\CreateSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\EditSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\ListSenders;
use AcMarche\MailingList\Models\Sender;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list'));
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the index page', function (): void {
    livewire(ListSenders::class)
        ->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateSender::class)
        ->assertOk();
});

it('can render the edit page', function (): void {
    $sender = Sender::factory()->create(['username' => $this->user->username]);

    livewire(EditSender::class, ['record' => $sender->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $sender->name,
            'email' => $sender->email,
        ]);
});

it('can list senders', function (): void {
    $senders = Sender::factory(3)->create(['username' => $this->user->username]);

    livewire(ListSenders::class)
        ->loadTable()
        ->assertCanSeeTableRecords($senders);
});

it('has table columns', function (string $column): void {
    livewire(ListSenders::class)
        ->assertTableColumnExists($column);
})->with(['name', 'email', 'created_at', 'updated_at']);

it('can sort column', function (string $column): void {
    $senders = Sender::factory(5)->create(['username' => $this->user->username]);

    livewire(ListSenders::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($senders->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($senders->sortByDesc($column), inOrder: true);
})->with(['name', 'email']);

it('can search senders', function (): void {
    $senders = Sender::factory(5)->create(['username' => $this->user->username]);

    $search = $senders->first()->name;

    livewire(ListSenders::class)
        ->loadTable()
        ->searchTable($search)
        ->assertCanSeeTableRecords($senders->where('name', $search))
        ->assertCanNotSeeTableRecords($senders->where('name', '!=', $search));
});

it('can create a sender', function (): void {
    $sender = Sender::factory()->make();

    livewire(CreateSender::class)
        ->fillForm([
            'name' => $sender->name,
            'email' => $sender->email,
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Sender::class, [
        'name' => $sender->name,
        'email' => $sender->email,
        'username' => $this->user->username,
    ]);
});

it('can create a sender with footer', function (): void {
    livewire(CreateSender::class)
        ->fillForm([
            'name' => 'Test Sender',
            'email' => 'sender@marche.be',
            'footer' => '<p>Custom footer content</p>',
        ])
        ->call('create')
        ->assertNotified();

    assertDatabaseHas(Sender::class, [
        'name' => 'Test Sender',
        'footer' => '<p>Custom footer content</p>',
    ]);
});

it('can update a sender', function (): void {
    $sender = Sender::factory()->create(['username' => $this->user->username]);
    $newData = Sender::factory()->make();

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(Sender::class, [
        'id' => $sender->id,
        'name' => $newData->name,
        'email' => $newData->email,
    ]);
});

it('can delete a sender', function (): void {
    $sender = Sender::factory()->create(['username' => $this->user->username]);

    livewire(EditSender::class, ['record' => $sender->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($sender);
});

it('can bulk delete senders', function (): void {
    $senders = Sender::factory(3)->create(['username' => $this->user->username]);

    livewire(ListSenders::class)
        ->loadTable()
        ->assertCanSeeTableRecords($senders)
        ->selectTableRecords($senders)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
        ->assertNotified()
        ->assertCanNotSeeTableRecords($senders);

    $senders->each(fn (Sender $sender) => assertDatabaseMissing($sender));
});

it('validates the form data', function (array $data, array $errors): void {
    $sender = Sender::factory()->create(['username' => $this->user->username]);
    $newData = Sender::factory()->make();

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`email` is required' => [['email' => null], ['email' => 'required']],
    '`email` is a valid email address' => [['email' => Str::random()], ['email' => 'email']],
    '`email` is max 255 characters' => [['email' => Str::random(256)], ['email' => 'max']],
]);
