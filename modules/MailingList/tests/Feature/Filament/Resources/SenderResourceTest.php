<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Senders\Pages\CreateSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\EditSender;
use AcMarche\MailingList\Filament\Resources\Senders\Pages\ViewSender;
use AcMarche\MailingList\Models\Sender;
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
