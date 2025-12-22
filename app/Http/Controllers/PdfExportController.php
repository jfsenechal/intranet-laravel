<?php

namespace App\Http\Controllers;

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Pdf\PdfExport;
use Spatie\LaravelPdf\PdfBuilder;

final class PdfExportController extends Controller
{
    public function downloadDeclaration(Declaration $declaration): PdfBuilder
    {
        return PdfExport::exportDeclaration($declaration); // returns BinaryFileResponse
    }
}
