<?php

declare(strict_types=1);

use AcMarche\Document\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('documents')->name('document.')->group(function (): void {
    Route::get('{document}', [DocumentController::class, 'show'])->name('show');
});
