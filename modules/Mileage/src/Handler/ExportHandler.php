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
     * @param array<string> $departments
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

            if (!$groupedData->has($username)) {
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
     * @param array<string> $departments
     */
    public function exportByYearPdf(int $year, array $departments = [], ?bool $omnium = null): PdfBuilder
    {
        $data = $this->byYear($year, $departments, $omnium);
        $name = 'recapitulatif-'.$year.'_'.'.pdf';

        return Pdf::view('mileage::filament.export.annual_declarations', [
            'year' => $year,
            'declarations' => $data['declarations'],
            'totalKilometers' => $data['totalKilometers'],
        ])
            //->download($name)
            ->save($name);
    }

    public function byUser(string $username)
    {
        $user = $this->userRepository->findByUsername($username);
        $profile = null;
        if ($user instanceof User) {
            $profile = $this->financeFrais->profileIsComplete($user);
            $username = $user->getUsername();
        } else {
            $user = $this->declarationRepository->getOneDeclarationByUsername($username);
        }
        $months = $this->intranet->getMonths();
        $date = new DateTime();
        $nextYear = $date->add(new DateInterval('P1Y'));
        $years = range(2016, $nextYear->format('Y'));
        $deplacements = [];
        $deplacements['interne'] = $this->getDeplacements($username, 'interne');
        $deplacements['externe'] = $this->getDeplacements($username, 'externe');
        $html = $this->renderView(
            'mileage::filament.export.user_declarations',
            [
                'user' => $user,
                'months' => $months,
                'years' => $years,
                'profile' => $profile,
                'deplacements' => $deplacements,
            ],
        );

        $name = "annees_".$username.'_'.Uuid::v4().".pdf";
        $filePath = $this->getTmpDir().'/'.$name;
        $this->generateAndSavePdf($html, $filePath, options: ['orientation' => 'landscape']);
    }
}
