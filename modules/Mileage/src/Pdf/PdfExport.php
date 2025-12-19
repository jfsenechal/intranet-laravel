<?php

namespace AcMarche\Mileage\Pdf;

use AcMarche\Mileage\Handler\Calculator;
use AcMarche\Mileage\Models\Declaration;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExport
{
    public static function exportDeclaration(Declaration $declaration): PdfBuilder
    {
        $calculator = new Calculator($declaration);
        $declarationSummary = $calculator->calculate();

        return Pdf::view('mileage::declaration-pdf', [
            'declaration' => $declaration,
            'declarationSummary' => $declarationSummary,
        ])
            // ->withBrowsershot(fn(Browsershot $shot) => $shot->setNodeBinary()->setNpmBinary()->setPuppeteerBinary()->setPuppeteerLaunchOptions([]))
            ->download('action-'.$declaration->id.'.pdf');
        // ->save('action-'.$action->id.'.pdf');
    }
}
