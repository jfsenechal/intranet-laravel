<?php

use App\Http\Controllers\PdfExportController;

Route::get('/download-action/{action}', [PdfExportController::class, 'download'])->name('download.action');
