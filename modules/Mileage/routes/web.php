<?php

use App\Http\Controllers\PdfExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/download-declaration/{declaration}', [PdfExportController::class, 'downloadDeclaration'])
        ->name('download.declaration');
});
