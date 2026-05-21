<?php

declare(strict_types=1);

use AcMarche\Conseil\Mail\ConseilNotificationMail;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Storage;

it('uses the given subject and renders the body', function (): void {
    $mail = new ConseilNotificationMail(
        'Conseil du XX - Groupe A',
        '<p>Bonjour à tous</p>',
    );

    $mail->assertHasSubject('Conseil du XX - Groupe A');
    $mail->assertSeeInHtml('Bonjour à tous');
});

it('attaches uploaded files from the storage disk', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('conseil/notifications/oj.pdf', 'pdf-content');

    $mail = new ConseilNotificationMail('Sujet', 'Corps', [
        ['disk' => 'local', 'path' => 'conseil/notifications/oj.pdf', 'name' => 'Ordre du jour.pdf'],
    ]);

    $attachments = $mail->attachments();

    expect($attachments)->toHaveCount(1)
        ->and($attachments[0])->toBeInstanceOf(Attachment::class)
        ->and($attachments[0]->as)->toBe('Ordre du jour.pdf');
});

it('has no attachments when no files are provided', function (): void {
    $mail = new ConseilNotificationMail('Sujet', 'Corps');

    expect($mail->attachments())->toBe([]);
});
