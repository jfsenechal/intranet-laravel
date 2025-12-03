<?php

namespace AcMarche\App;

use AcMarche\Mileage\Models\Declaration;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExport
{
    public static function exportPublication(Declaration $declaration): PdfBuilder
    {
        return Pdf::html(view('pdf.declaration', [
            'declaration' => $declaration,
        ]))
            // ->withBrowsershot(fn(Browsershot $shot) => $shot->setNodeBinary()->setNpmBinary()->setPuppeteerBinary()->setPuppeteerLaunchOptions([]))
            ->download('declaration-'.$declaration->id.'.pdf');
        // ->save('action-'.$action->id.'.pdf');
    }
}
