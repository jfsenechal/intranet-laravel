<?php

declare(strict_types=1);

namespace AcMarche\Issep\Enums;

use Filament\Support\Colors\Color;

/**
 * The BelAQI air quality index, from 1 (excellent) to 10 (appalling).
 *
 * The labels and colours are the ones the Walloon region publishes for the index, kept
 * identical to the legacy intranet so a reading looks the same in both.
 */
enum IndiceEnum: int
{
    case NO_VALID = -1;
    case NO_DATA = 0;
    case EXCELLENT = 1;
    case VERY_GOOD = 2;
    case GOOD = 3;
    case FAIRLY_GOOD = 4;
    case AVERAGE = 5;
    case INSUFFICIENT = 6;
    case QUITE_POOR = 7;
    case POOR = 8;
    case VERY_POOR = 9;
    case APPALLING = 10;

    /**
     * Any value the API sends that is not a known index degrades to "no data" rather than
     * failing the whole table.
     */
    public static function fromAqiValue(int|string|null $aqiValue): self
    {
        if ($aqiValue === null || ! is_numeric($aqiValue)) {
            return self::NO_DATA;
        }

        return self::tryFrom((int) $aqiValue) ?? self::NO_DATA;
    }

    /**
     * The BelAQI colours registered on the panel, so a badge can be told to use them by name.
     *
     * @return array<string, array<int, string>>
     */
    public static function panelColors(): array
    {
        $colors = [];

        foreach (self::cases() as $case) {
            $colors[$case->colorName()] = Color::hex($case->hex());
        }

        return $colors;
    }

    public function label(): string
    {
        return match ($this) {
            self::EXCELLENT => 'Excellent',
            self::VERY_GOOD => 'Très bon',
            self::GOOD => 'Bien',
            self::FAIRLY_GOOD => 'Assez bon',
            self::AVERAGE => 'Moyen',
            self::INSUFFICIENT => 'Insuffisant',
            self::QUITE_POOR => 'Assez mauvais',
            self::POOR => 'Mauvais',
            self::VERY_POOR => 'Très mauvais',
            self::APPALLING => 'Exécrable',
            self::NO_DATA, self::NO_VALID => 'Non valide',
        };
    }

    public function hex(): string
    {
        return match ($this) {
            self::EXCELLENT => '#0000FF',
            self::VERY_GOOD => '#00BFFF',
            self::GOOD => '#00FF00',
            self::FAIRLY_GOOD => '#ADFF2F',
            self::AVERAGE => '#FFFF00',
            self::INSUFFICIENT => '#FFA500',
            self::QUITE_POOR => '#FF4500',
            self::POOR => '#FF0000',
            self::VERY_POOR => '#8B0000',
            self::APPALLING => '#8B008B',
            self::NO_DATA, self::NO_VALID => '#A6ACAF',
        };
    }

    /**
     * The name this index is registered under in the panel's colour palette. Both invalid
     * values share the grey of "no data", so they share its name too.
     */
    public function colorName(): string
    {
        return match ($this) {
            self::NO_DATA, self::NO_VALID => 'belaqi-none',
            default => 'belaqi-'.$this->value,
        };
    }

    /**
     * The traffic light of the legacy map: green up to "assez bon", yellow up to "assez
     * mauvais", red beyond.
     */
    public function trafficLight(): string
    {
        return match ($this) {
            self::EXCELLENT, self::VERY_GOOD, self::GOOD, self::FAIRLY_GOOD => 'green',
            self::AVERAGE, self::INSUFFICIENT, self::QUITE_POOR => 'yellow',
            self::POOR, self::VERY_POOR, self::APPALLING => 'red',
            self::NO_DATA, self::NO_VALID => 'grey',
        };
    }

    public function hasReading(): bool
    {
        return $this !== self::NO_DATA && $this !== self::NO_VALID;
    }
}
