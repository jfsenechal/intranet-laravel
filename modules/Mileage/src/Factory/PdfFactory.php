<?php

namespace AcMarche\Mileage\Factory;

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Service\ExportDataAggregator;
use Spatie\LaravelPdf\Facades\Pdf;

final class PdfFactory
{
    private ExportDataAggregator $exportHandler;

    public function __construct()
    {
        $this->exportHandler = new ExportDataAggregator();
    }

    public function createFromDeclaration(Declaration $declaration): array
    {
        $declaration->load('trips');
        $calculator = new DeclarationCalculator($declaration);
        $declarationSummary = $calculator->calculate();
        $name = 'deplacement-'.$declaration->user_add.'-'.$declaration->created_at->format('d-m-Y').'.pdf';

        $directory = storage_path('app/private/mileage/exports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/'.$name;

        Pdf::view('mileage::declaration-pdf', [
            'declaration' => $declaration,
            'declarationSummary' => $declarationSummary,
        ])
            ->save($path);

        return [
            'path' => $path,
            'name' => $name,
        ];
    }

    /**
     * Generate PDF for annual declarations recap.
     *
     * @param  array<string>  $departments
     */
    public function createByYear(int $year, array $departments = [], ?bool $omnium = null): array
    {
        $data = $this->exportHandler->byYear($year, $departments, $omnium);
        $name = 'recapitulatif-'.$year.'_'.'.pdf';

        $directory = storage_path('app/private/mileage/exports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/'.$name;

        Pdf::view('mileage::filament.export.annual_declarations-pdf', [
            'year' => $year,
            'declarations' => $data['declarations'],
            'totalKilometers' => $data['totalKilometers'],
        ])
            ->save($path);

        return [
            'path' => $path,
            'name' => $name,
        ];
    }

    /**
     * Generate PDF for user declarations recap.
     *
     * @return array{path: string, name: string}
     */
    public function createByUser(string $username): array
    {
        $data = $this->exportHandler->byUser($username);
        $name = 'declarations-'.$username.'.pdf';
        $directory = storage_path('app/private/mileage/exports');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory.'/'.$name;

        Pdf::view('mileage::filament.export.user_declarations-pdf', [
            'username' => $username,
            'declaration' => $data['declaration'],
            'months' => $data['months'],
            'years' => $data['years'],
            'deplacements' => $data['deplacements'],
        ])
            ->landscape()
            ->save($path);

        return [
            'path' => $path,
            'name' => $name,
        ];
    }
}
