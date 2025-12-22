<?php

namespace AcMarche\Mileage\Pdf;

use AcMarche\Mileage\Handler\Calculator;
use AcMarche\Mileage\Models\Declaration;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExport
{
    public static function exportDeclaration(Declaration $declaration): PdfBuilder|Pdf
    {
        $declaration->load('trips');
        $calculator = new Calculator($declaration);
        $declarationSummary = $calculator->calculate();
        $name = 'deplacement-'.$declaration->user_add.'-'.$declaration->created_at->format('d-m-Y').'.pdf';

        return Pdf::view('mileage::declaration-pdf', [
            'declaration' => $declaration,
            'declarationSummary' => $declarationSummary,
        ])
            // ->withBrowsershot(fn(Browsershot $shot) => $shot->setNodeBinary()->setNpmBinary()->setPuppeteerBinary()->setPuppeteerLaunchOptions([]))
            //   ->download($name);
            ->save($name);
    }
}
