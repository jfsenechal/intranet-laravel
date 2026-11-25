<?php

declare(strict_types=1);

use AcMarche\Ad\Http\Controllers\PublicClassifiedAdController;
use AcMarche\Ad\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('annonces')->name('ad.')->group(function (): void {
    Route::get('abonnement', [SubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('abonnement/subscribe', [SubscriptionController::class, 'subscribe'])
        ->name('subscription.subscribe');
    Route::post('abonnement/unsubscribe', [SubscriptionController::class, 'unsubscribe'])
        ->name('subscription.unsubscribe');

    Route::get('deposer', [PublicClassifiedAdController::class, 'show'])->name('public.create');
    Route::post('deposer/identification', [PublicClassifiedAdController::class, 'identify'])
        ->middleware('throttle:10,1')
        ->name('public.identify');
    Route::post('deposer/changer-email', [PublicClassifiedAdController::class, 'forget'])
        ->name('public.forget');
    Route::post('deposer', [PublicClassifiedAdController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('public.store');
});
