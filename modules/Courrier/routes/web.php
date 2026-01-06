<?php

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
});
