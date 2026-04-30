<?php

declare(strict_types=1);

use AcMarche\Courrier\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('courrier')->name('courrier.')->group(function (): void {
    // IMAP attachment routes (for inbox preview)
    Route::get('attachments/{uid}/{index}', [AttachmentController::class, 'show'])
        ->name('attachments.show');

    Route::get('attachments/{uid}/{index}/preview', [AttachmentController::class, 'preview'])
        ->name('attachments.preview');

    // Saved attachment download route
    Route::get('attachments/download/{attachment}', [AttachmentController::class, 'download'])
        ->name('attachments.download');

    // Saved attachment inline preview route
    Route::get('attachments/preview/{attachment}', [AttachmentController::class, 'previewStored'])
        ->name('attachments.preview-stored');
});
