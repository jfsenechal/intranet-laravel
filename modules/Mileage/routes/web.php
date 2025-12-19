<?php

use App\Http\Controllers\PdfExportController;

Route::get('/download-declaration/{declaration}', [PdfExportController::class, 'download'])->name('download.declaration');
