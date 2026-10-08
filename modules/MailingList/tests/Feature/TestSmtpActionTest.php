<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Senders\Pages\ViewSender;
use AcMarche\MailingList\Models\Sender;
use Filament\Actions\Testing\TestAction;
use Illuminate\Mail\Mailer;

use function Pest\Livewire\livewire;

it('shows the test SMTP action in the configuration section', function (): void {
    $sender = Sender::factory()->create(['username' => auth()->user()->username]);

    livewire(ViewSender::class, ['record' => $sender->id])
        ->assertActionExists(
            TestAction::make('testSmtp')->schemaComponent('smtpConfiguration'),
        );
});

it('sends a test email through the default mailer and reports success', function (): void {
    $sender = Sender::factory()->create(['username' => auth()->user()->username]);

    livewire(ViewSender::class, ['record' => $sender->id])
        ->callAction(
            TestAction::make('testSmtp')->schemaComponent('smtpConfiguration'),
            ['email' => 'test@example.com'],
        )
        ->assertHasNoActionErrors()
        ->assertNotified('Test SMTP réussi');
});

it('reports a failure when the smtp server is unreachable', function (): void {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
        'smtp_host' => '127.0.0.1',
        'smtp_port' => 1,
        'smtp_username' => 'user@example.com',
        'smtp_password' => 'secret',
    ]);

    livewire(ViewSender::class, ['record' => $sender->id])
        ->callAction(
            TestAction::make('testSmtp')->schemaComponent('smtpConfiguration'),
            ['email' => 'test@example.com'],
        )
        ->assertHasNoActionErrors()
        ->assertNotified('Échec du test SMTP');
});

it('validates that a test email address is required', function (): void {
    $sender = Sender::factory()->create(['username' => auth()->user()->username]);

    livewire(ViewSender::class, ['record' => $sender->id])
        ->callAction(
            TestAction::make('testSmtp')->schemaComponent('smtpConfiguration'),
            ['email' => null],
        )
        ->assertHasActionErrors(['email' => ['required']]);
});

it('configures a dedicated mailer from the sender smtp settings', function (): void {
    $sender = Sender::factory()->create([
        'username' => auth()->user()->username,
        'smtp_host' => 'smtp.marche.be',
        'smtp_port' => 465,
        'smtp_username' => 'user@marche.be',
        'smtp_password' => 'secret',
    ]);

    $mailer = $sender->resolveMailer();

    expect($mailer)->toBeInstanceOf(Mailer::class)
        ->and(config("mail.mailers.sender_{$sender->id}"))
        ->toMatchArray([
            'transport' => 'smtp',
            'host' => 'smtp.marche.be',
            'port' => 465,
            'username' => 'user@marche.be',
        ]);
});
