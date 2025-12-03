<?php

namespace App\Http\Controllers;

use AcMarche\App\PdfExport;
use AcMarche\Mileage\Models\Declaration;
use Spatie\LaravelPdf\PdfBuilder;

class PdfExportController extends Controller
{
    public function download(Declaration $action): PdfBuilder
    {
        return PdfExport::exportPublication($action); // returns BinaryFileResponse
    }
}
