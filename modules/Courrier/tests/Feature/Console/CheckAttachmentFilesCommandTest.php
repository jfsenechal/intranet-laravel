<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Search\AttachmentOcr;
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

it('does not look at the OCR cache without the --ocr option', function (): void {
    fakeCheckDisk();
    Storage::disk('check-test')->put('indicateur/ville/3/doc.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/3/doc.pdf',
    ]);

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id])
        ->doesntExpectOutputToContain('without OCR text')
        ->assertExitCode(Command::SUCCESS);
});

it('passes with --ocr when the cached OCR text is newer than the file', function (): void {
    fakeCheckDisk();
    Storage::disk('check-test')->put('indicateur/ville/4/doc.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/4/doc.pdf',
    ]);

    Storage::disk('check-test')->put(AttachmentOcr::cachePathFor($attachment), 'extracted text');

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id, '--ocr' => true])
        ->expectsOutputToContain('0 attachments without OCR text.')
        ->assertExitCode(Command::SUCCESS);
});

it('reports an attachment with no cached OCR text', function (): void {
    fakeCheckDisk();
    Storage::disk('check-test')->put('indicateur/ville/5/doc.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/5/doc.pdf',
    ]);

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id, '--ocr' => true])
        ->expectsOutputToContain('no OCR text at "'.AttachmentOcr::cachePathFor($attachment).'"')
        ->expectsOutputToContain('Checked 1 attachments, 0 mismatched.')
        ->expectsOutputToContain('1 attachments without OCR text.')
        ->assertExitCode(Command::FAILURE);
});

it('reports an attachment whose cached OCR text is older than the file', function (): void {
    fakeCheckDisk();
    $disk = Storage::disk('check-test');
    $disk->put('indicateur/ville/6/doc.pdf', 'pdf');

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'indicateur/ville/6/doc.pdf',
    ]);

    $disk->put(AttachmentOcr::cachePathFor($attachment), 'stale text');
    touch($disk->path(AttachmentOcr::cachePathFor($attachment)), time() - 3600);

    $this->artisan('courrier:check-attachment-files', ['--id' => $attachment->id, '--ocr' => true])
        ->expectsOutputToContain('OCR text is older than the file "doc.pdf"')
        ->expectsOutputToContain('1 attachments without OCR text.')
        ->assertExitCode(Command::FAILURE);
});
