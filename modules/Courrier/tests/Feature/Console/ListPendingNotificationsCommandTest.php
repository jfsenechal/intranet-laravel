<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use Symfony\Component\Console\Command\Command;

test('it lists a recipient with pending mail along with the subject', function (): void {
    $recipient = Recipient::factory()->create([
        'email' => 'pending@example.com',
        'last_name' => 'Doe',
        'first_name' => 'Jane',
    ]);

    $mail = IncomingMail::factory()->create([
        'mail_date' => now(),
        'is_notified' => false,
    ]);
    $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

    $this->artisan('courrier:notifications:list')
        ->expectsOutputToContain('pending@example.com')
        ->expectsOutputToContain('1 recipient(s) would be notified about 1 incoming mail(s).')
        ->assertExitCode(Command::SUCCESS);
});

test('it reports when nothing would be sent for the given date', function (): void {
    IncomingMail::factory()->create([
        'mail_date' => now(),
        'is_notified' => false,
    ]);

    $this->artisan('courrier:notifications:list', ['--date' => now()->format('Y-m-d')])
        ->expectsOutputToContain('No notification would be sent for this date.')
        ->assertExitCode(Command::SUCCESS);
});

test('it does not mark mail as notified', function (): void {
    $recipient = Recipient::factory()->create([
        'email' => 'noop@example.com',
    ]);

    $mail = IncomingMail::factory()->create([
        'mail_date' => now(),
        'is_notified' => false,
    ]);
    $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

    $this->artisan('courrier:notifications:list')
        ->assertExitCode(Command::SUCCESS);

    expect($mail->fresh()->is_notified)->toBeFalse();
});

test('it warns about recipients without an email address', function (): void {
    Recipient::factory()->create(['email' => null]);

    $this->artisan('courrier:notifications:list')
        ->expectsOutputToContain('have no e-mail address and are skipped entirely.')
        ->assertExitCode(Command::SUCCESS);
});
