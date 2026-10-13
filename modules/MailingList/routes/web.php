<?php

declare(strict_types=1);

use AcMarche\MailingList\Http\Controllers\UnsubscribeController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'signed'])->group(function (): void {
    Route::get('/unsubscribe/{recipient}', [UnsubscribeController::class, 'show'])
        ->name('unsubscribe.show');
    // The signed URL already authenticates the request; mail clients sending a
    // List-Unsubscribe-Post one-click request carry no CSRF token.
    Route::post('/unsubscribe/{recipient}', [UnsubscribeController::class, 'store'])
        ->withoutMiddleware(PreventRequestForgery::class)
        ->name('unsubscribe.store');
});
