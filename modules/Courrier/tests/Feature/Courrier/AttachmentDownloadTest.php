<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Support\Facades\Storage;

function createStoredAttachment(string $path, string $fileName = 'doc.pdf'): Attachment
{
    $mail = IncomingMail::factory()->create();

    return Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => $fileName,
        'mime' => 'application/pdf',
        'path' => $path,
    ]);
}

it('serves the file at the attachment path column, honouring the legacy folder layout', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/ville/142854/indicateur-ville-6a4e1274d15b43.63314926.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachment($path);

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('doc.pdf');
});

it('returns 404 when the stored file is missing', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    Storage::fake(config('courrier.storage.disk'));

    $attachment = createStoredAttachment('courrier/ville/1/missing.pdf');

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertNotFound();
});

it('serves the stored preview inline from the path column', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/attachments/1_new-upload.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachment($path, '1_new-upload.pdf');

    $this->get(route('courrier.attachments.preview-stored', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
