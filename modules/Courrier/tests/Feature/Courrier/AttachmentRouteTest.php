<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;

it('routes the saved attachment download url to the download action, not the imap show route', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    $mail = IncomingMail::factory()->create();
    $attachment = Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
    ]);

    $url = route('courrier.attachments.download', $attachment);

    $response = $this->get($url);

    // The stored file does not exist on disk, so the download action returns 404.
    // Before the numeric route constraints, this url was captured by the
    // `attachments/{uid}/{index}` (show) route with a non-numeric `uid`,
    // which threw a TypeError (HTTP 500).
    $response->assertNotFound();

    expect(app('router')->getRoutes()->match(
        Illuminate\Http\Request::create($url)
    )->getName())->toBe('courrier.attachments.download');
});

it('rejects a non-numeric uid on the imap show route', function (): void {
    $this->get('/courrier/attachments/download/142854')
        ->assertNotFound();
});
