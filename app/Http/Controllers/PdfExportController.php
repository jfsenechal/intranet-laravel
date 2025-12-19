<?php

namespace App\Http\Controllers;

use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Pdf\PdfExport;
use Spatie\LaravelPdf\PdfBuilder;

class PdfExportController extends Controller
{
    public function download(Declaration $action): PdfBuilder
    {
        return PdfExport::exportDeclaration($action); // returns BinaryFileResponse
    }
}
