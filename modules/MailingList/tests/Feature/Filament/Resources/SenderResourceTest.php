<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Senders\Pages\CreateSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\EditSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\ViewSender;
use AcMarche\MailingList\Models\Sender;
use AcMarche\MailingList\Models\Signature;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('can render the view page', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(ViewSender::class, [
        'record' => $sender->id,
    ])
        ->assertOk()
        ->assertSee($sender->name)
        ->assertSee($sender->email);
});

it('hides senders owned by another user from the view page', function () {
    $sender = Sender::factory()->create([
        'username' => User::factory()->create()->username,
    ]);

    livewire(ViewSender::class, [
        'record' => $sender->id,
    ])->assertNotFound();
});

it('can create a sender with smtp settings', function () {
    livewire(CreateSender::class)
        ->fillForm([
            'name' => 'Service Communication',
            'email' => 'communication@marche.be',
            'smtp_host' => 'smtp.marche.be',
            'smtp_port' => 587,
            'smtp_username' => 'communication@marche.be',
            'smtp_password' => 'secret-password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Sender::class, [
        'name' => 'Service Communication',
        'email' => 'communication@marche.be',
        'smtp_host' => 'smtp.marche.be',
        'smtp_port' => 587,
        'smtp_username' => 'communication@marche.be',
    ]);
});

it('encrypts the smtp password in the database', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
        'smtp_host' => 'smtp.marche.be',
        'smtp_username' => 'user@marche.be',
        'smtp_password' => 'plain-text-secret',
    ]);

    $raw = Illuminate\Support\Facades\DB::table('senders')
        ->where('id', $sender->id)
        ->value('smtp_password');

    expect($raw)->not->toBe('plain-text-secret')
        ->and($sender->fresh()->smtp_password)->toBe('plain-text-secret');
});

it('detects when smtp settings are configured', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
        'smtp_host' => 'smtp.marche.be',
        'smtp_username' => 'user@marche.be',
        'smtp_password' => 'secret',
    ]);

    expect($sender->hasSmtpSettings())->toBeTrue();
});

it('detects when smtp settings are missing', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);

    expect($sender->hasSmtpSettings())->toBeFalse();
});

it('can update smtp settings on edit page', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm([
            'smtp_host' => 'smtp.marche.be',
            'smtp_port' => 465,
            'smtp_username' => 'sender@marche.be',
            'smtp_password' => 'new-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Sender::class, [
        'id' => $sender->id,
        'smtp_host' => 'smtp.marche.be',
        'smtp_port' => 465,
        'smtp_username' => 'sender@marche.be',
    ]);
});

it('prefills the create form with the current user when they have no sender', function () {
    $user = auth()->user();

    livewire(CreateSender::class)
        ->assertSchemaStateSet([
            'name' => mb_trim($user->first_name.' '.$user->last_name),
            'email' => $user->email,
            'username' => $user->username,
        ]);
});

it('does not prefill the create form when the user already has a sender', function () {
    Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(CreateSender::class)
        ->assertSchemaStateSet([
            'name' => null,
            'email' => null,
        ]);
});

function makeSignatureFor(string $username): Signature
{
    return Signature::create([
        'username' => $username,
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'catherine.boldo@cpas.marche.be',
    ]);
}

it('attaches one of the user signatures to a sender', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $signature = makeSignatureFor(auth()->user()->username);

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm(['signature_id' => $signature->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($sender->refresh()->signature_id)->toBe($signature->id);
});

it('detaches the signature from a sender', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
        'signature_id' => makeSignatureFor(auth()->user()->username)->id,
    ]);

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm(['signature_id' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($sender->refresh()->signature_id)->toBeNull();
});

it('rejects a signature owned by another user', function () {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);
    $foreignSignature = makeSignatureFor(User::factory()->create()->username);

    livewire(EditSender::class, ['record' => $sender->id])
        ->fillForm(['signature_id' => $foreignSignature->id])
        ->call('save')
        ->assertHasFormErrors(['signature_id']);

    expect($sender->refresh()->signature_id)->toBeNull();
});
