<?php

declare(strict_types=1);

use AcMarche\Courrier\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('courrier')->name('courrier.')->group(function (): void {
    // IMAP attachment routes (for inbox preview)
    Route::get('attachments/{uid}/{index}', [AttachmentController::class, 'show'])
        ->whereNumber('uid')
        ->whereNumber('index')
        ->name('attachments.show');

    Route::get('attachments/{uid}/{index}/preview', [AttachmentController::class, 'preview'])
        ->whereNumber('uid')
        ->whereNumber('index')
        ->name('attachments.preview');

    // Saved attachment download route
    Route::get('attachments/download/{attachment}', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    // Saved attachment inline preview route
    Route::get('attachments/preview/{attachment}', [AttachmentController::class, 'previewStored'])
        ->name('attachments.preview-stored');
});

// Legacy indicateur URL: /{department}/indicateur/courrier/{id} (e.g. /ville/indicateur/courrier/142853).
// The department segment is kept only for backward compatibility; the courrier id is the
// global primary key and is enough to resolve the record on the new Filament view page.
Route::middleware('web')->get('{department}/indicateur/courrier/{id}', function (string $department, int $id) {
    return redirect()->route(
        'filament.courrier-panel.resources.incoming-mails.view',
        ['record' => $id],
        301,
    );
})
    ->whereNumber('id')
    ->where('department', '(?i)(ville|cpas|bgm)')
    ->name('courrier.legacy.view');
