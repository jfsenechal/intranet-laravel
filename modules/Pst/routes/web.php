<?php

declare(strict_types=1);

use AcMarche\Pst\Http\Controllers\PdfExportController;
use AcMarche\Pst\Http\Controllers\SelectDepartmentController;
use Illuminate\Support\Facades\Route;

Route::get('/export-action/{action}', [PdfExportController::class, 'export'])->name('export.action');
Route::get('/pdf-download/{path}', [PdfExportController::class, 'download'])->name('pdf.download')->where('path', '.*');
Route::get('/admin/select/department/{department}', [SelectDepartmentController::class, 'select'])
    ->name('select.department');
