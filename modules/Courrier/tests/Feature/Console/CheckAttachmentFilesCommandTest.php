<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command;

function fakeCheckDisk(): void
{
    config()->set('courrier.storage.disk', 'check-test');
    Storage::fake('check-test');
}

it('passes when the stored path points at an existing file', function (): void {
    fakeCheckDisk();
    Storage::disk('check-test')->put('indicateur/ville/1/doc.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/1/doc.pdf',
    ]);

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id])
        ->expectsOutputToContain('Checked 1 attachments, 0 mismatched.')
        ->assertExitCode(Command::SUCCESS);
});

it('flags an attachment whose stored file is missing and surfaces the real name', function (): void {
    fakeCheckDisk();
    Storage::disk('check-test')->put('indicateur/ville/2/regenerated.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'stale.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/2/stale.pdf',
    ]);

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id])
        ->expectsOutputToContain('actual file: regenerated.pdf')
        ->expectsOutputToContain('Checked 1 attachments, 1 mismatched.')
        ->assertExitCode(Command::FAILURE);
});
