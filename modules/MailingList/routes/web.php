<?php

declare(strict_types=1);

use AcMarche\MailingList\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

Route::middleware('signed')->group(function (): void {
    Route::get('/unsubscribe/{recipient}', [UnsubscribeController::class, 'show'])
        ->name('unsubscribe.show');
    Route::post('/unsubscribe/{recipient}', [UnsubscribeController::class, 'store'])
        ->name('unsubscribe.store');
});
