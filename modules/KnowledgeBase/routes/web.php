<?php

declare(strict_types=1);

use AcMarche\KnowledgeBase\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('knowledge-base')->name('knowledge-base.')->group(function (): void {
    Route::get('images/{filename}', ImageController::class)
        ->where('filename', '[0-9a-f\-]{36}\.(png|jpg|gif|webp)')
        ->name('image');
});
