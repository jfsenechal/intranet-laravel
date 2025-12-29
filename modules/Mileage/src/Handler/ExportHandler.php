<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Repository\DeclarationRepository;
use Illuminate\Support\Collection;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

final class ExportHandler
{
    /**
     * Export declarations by year as PDF.
     *
     * @param  array<string>  $departments
     * @return array{declarations: Collection<int, array<string, mixed>>, totalKilometers: int}
     */
    public function byYear(int $year, array $departments = [], ?bool $omnium = null): array
    {
        $declarations = DeclarationRepository::findByYear($year, $departments, $omnium);

        $groupedData = collect();
        $totalKilometers = 0;

        foreach ($declarations as $declaration) {
            $username = $declaration->user_add;
            $tripKilometers = $declaration->trips->sum('kilometers');

            if (! $groupedData->has($username)) {
                $groupedData[$username] = [
                    'distance' => 0,
                    'last_name' => $declaration->last_name,
                    'first_name' => $declaration->first_name,
                    'car_license_plate1' => $declaration->car_license_plate1,
                    'car_license_plate2' => $declaration->car_license_plate2,
                    'omnium' => $declaration->omnium,
                ];
            }

            $groupedData[$username] = array_merge($groupedData[$username], [
                'distance' => $groupedData[$username]['distance'] + $tripKilometers,
            ]);

            $totalKilometers += $tripKilometers;
        }

        return [
            'declarations' => $groupedData,
            'totalKilometers' => $totalKilometers,
        ];
    }

    /**
     * Generate PDF for annual declarations recap.
     *
     * @param  array<string>  $departments
     */
    public function exportByYearPdf(int $year, array $departments = [], ?bool $omnium = null): PdfBuilder
    {
        $data = $this->byYear($year, $departments, $omnium);
        $name = 'recapitulatif-'.$year.'_'.'.pdf';

        return Pdf::view('mileage::filament.export.annual_declarations-pdf', [
            'year' => $year,
            'declarations' => $data['declarations'],
            'totalKilometers' => $data['totalKilometers'],
        ])
            // ->download($name)
            ->save($name);
    }

    /**
     * Get data for user export.
     *
     * @return array{
     *     declaration: \AcMarche\Mileage\Models\Declaration|null,
     *     months: array<int, string>,
     *     years: array<int>,
     *     deplacements: array{interne: array<int, array<int, int>>, externe: array<int, array<int, int>>}
     * }
     */
    public function byUser(string $username): array
    {
        $declaration = DeclarationRepository::getOneDeclarationByUsername($username);

        $months = $this->getMonths();
        $years = range(2016, (int) date('Y') + 1);

        $deplacements = [
            'interne' => DeclarationRepository::getKilometersByYearMonth($username, 'interne'),
            'externe' => DeclarationRepository::getKilometersByYearMonth($username, 'externe'),
        ];

        return [
            'declaration' => $declaration,
            'months' => $months,
            'years' => $years,
            'deplacements' => $deplacements,
        ];
    }

    /**
     * Generate PDF for user declarations recap.
     *
     * @return array{path: string, name: string}
     */
    public function exportByUserPdf(string $username): array
    {
        $data = $this->byUser($username);
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

    /**
     * Get months array.
     *
     * @return array<int, string>
     */
    private function getMonths(): array
    {
        return [
            1 => 'Jan',
            2 => 'Fév',
            3 => 'Mar',
            4 => 'Avr',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juil',
            8 => 'Août',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Déc',
        ];
    }
}
