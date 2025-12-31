<?php

use AcMarche\Courrier\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('courrier')->name('courrier.')->group(function (): void {
    Route::get('attachments/{uid}/{index}', [AttachmentController::class, 'show'])
        ->name('attachments.show');

    Route::get('attachments/{uid}/{index}/preview', [AttachmentController::class, 'preview'])
        ->name('attachments.preview');
});
