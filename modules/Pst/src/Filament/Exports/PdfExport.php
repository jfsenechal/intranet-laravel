<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Exports;

use AcMarche\Pst\Models\Action;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

final class PdfExport
{
    public static function exportAction(Action $action): string
    {
        $filename = 'action-'.$action->id.'-'.time().'.pdf';
        $relativePath = 'pdf/'.$filename;

        Storage::disk('public')->makeDirectory('pdf');

        $fullPath = Storage::disk('public')->path($relativePath);

        Pdf::html(
            view('pdf.action', [
                'action' => $action,
            ])->render()
        )
            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            })
            ->save($fullPath);

        return $relativePath;
    }
}
