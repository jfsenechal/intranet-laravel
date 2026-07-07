<?php

declare(strict_types=1);

use AcMarche\News\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('actualites')->name('news.')->group(function (): void {
    Route::get('{news}', [NewsController::class, 'show'])->name('show');
});
