<?php

declare(strict_types=1);

namespace AcMarche\Issep\Support;

use Illuminate\Support\Str;

/**
 * What each field of a raw ISSEP measurement means, in French.
 *
 * The descriptions come from the ISSEP data dictionary as the legacy intranet reproduced it.
 * Lookups are normalised (case and underscores dropped) because the API is not consistent
 * between endpoints: the same field appears as `BME_t`, `bme_t` or `bmeT`. A field that has
 * no description is still displayed, under its own name.
 */
final class MeasurementLabels
{
    /**
     * @var array<string, string>
     */
    private const DESCRIPTIONS = [
        'idconfiguration' => 'Identifiant de la configuration',
        'idreseau' => 'Identifiant du réseau',
        'userid' => "Identifiant de l'utilisateur",
        'moment' => 'Horodatage de la mesure',
        'no' => 'Signal brut de mesure du NO (monoxyde d’azote)',
        'no2' => 'Signal brut de mesure du NO2 (dioxyde d’azote)',
        'o3no2' => 'Signal brut de mesure de l’O3 (ozone)',
        'co' => 'Signal brut de mesure du CO (monoxyde de carbone)',
        'ppbno' => 'Concentration en NO exprimée en ppb (parts par milliard)',
        'ppbno2' => 'Concentration en NO2 exprimée en ppb (parts par milliard)',
        'ppbo3' => 'Concentration en O3 exprimée en ppb (parts par milliard)',
        'ppbo3no2' => 'Concentration en O3 exprimée en ppb (parts par milliard)',
        'ugpcmno' => 'Concentration en NO exprimée en µg/m³',
        'ugpcmno2' => 'Concentration en NO2 exprimée en µg/m³',
        'ugpcmo3' => 'Concentration en O3 exprimée en µg/m³',
        'ugpcmo3no2' => 'Concentration en O3 exprimée en µg/m³',
        'bmet' => 'Température exprimée en °C',
        'bmepres' => 'Pression atmosphérique exprimée en Pa',
        'bmerh' => 'Humidité relative exprimée en %',
        'pm1' => 'Concentration en particules de type PM1 exprimée en µg/m³',
        'pm25' => 'Concentration en particules de type PM2.5 exprimée en µg/m³',
        'pm4' => 'Concentration en particules de type PM4 exprimée en µg/m³',
        'pm10' => 'Concentration en particules de type PM10 exprimée en µg/m³',
        'temp' => 'Signal brut de mesure de la température par un 2nd capteur',
        'vbat' => 'Tension de la batterie (V)',
        'mwhbat' => 'Énergie consommée par la batterie (mWh)',
        'mwhpv' => 'Énergie fournie par le panneau photovoltaïque (mWh)',
        'corf' => 'Valeur de référence du CO',
        'norf' => 'Valeur de référence du NO',
        'no2rf' => 'Valeur de référence du NO2',
        'o3rf' => 'Valeur de référence de l’O3',
        'o3no2rf' => 'Valeur de référence de l’O3',
        'pm10rf' => 'Valeur de référence des PM10',
    ];

    /**
     * A measurement row keyed on normalised field names.
     *
     * The raw measurement endpoint spells a field "ppbNo" where /lastdata spells it "ppbno",
     * so a table cannot address a column by the spelling of one endpoint. Normalising the row
     * once lets the columns use a single canonical key.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $field => $value) {
            $normalized[self::normalizeKey((string) $field)] = $value;
        }

        return $normalized;
    }

    public static function normalizeKey(string $field): string
    {
        return Str::lower(str_replace(['_', '-', ' '], '', $field));
    }

    public static function describe(string $field): ?string
    {
        $normalized = self::normalizeKey($field);

        if (isset(self::DESCRIPTIONS[$normalized])) {
            return self::DESCRIPTIONS[$normalized];
        }

        /*
         * Every measurement is doubled by a "_statut" field qualifying it, which the data
         * dictionary does not describe one by one.
         */
        if (Str::endsWith($normalized, 'statut')) {
            $measurement = Str::beforeLast($normalized, 'statut');

            return isset(self::DESCRIPTIONS[$measurement])
                ? 'Statut de la mesure: '.lcfirst(self::DESCRIPTIONS[$measurement])
                : 'Statut de la mesure';
        }

        return null;
    }
}
