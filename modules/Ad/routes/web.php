<?php

declare(strict_types=1);

use AcMarche\Ad\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('annonces')->name('ad.')->group(function (): void {
    Route::get('abonnement', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('abonnement/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('subscription.subscribe');
    Route::post('abonnement/unsubscribe', [SubscriptionController::class, 'unsubscribe'])
        ->name('subscription.unsubscribe');
});
