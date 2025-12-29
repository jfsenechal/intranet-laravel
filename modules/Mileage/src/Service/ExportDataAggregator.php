<?php

namespace AcMarche\Mileage\Service;

use AcMarche\Mileage\Repository\DeclarationRepository;
use Illuminate\Support\Collection;

final class ExportDataAggregator
{
    /**
     * Get data for declarations by year.
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
