<?php

declare(strict_types=1);

namespace AcMarche\Issep\Dto;

use Carbon\CarbonImmutable;
use Exception;

/**
 * A microsensor station, as returned by the /locations endpoint.
 *
 * `id` identifies the station (it is what the intranet puts in its URLs), while
 * `idConfiguration` identifies the sensor configuration currently attributed to it and is
 * what every measurement endpoint is keyed by.
 */
final readonly class Station
{
    public function __construct(
        public int $id,
        public string $nom,
        public int $idReseau,
        public int $idConfiguration,
        public ?string $x,
        public ?string $y,
        public ?float $lat,
        public ?float $lon,
        public ?string $altitude,
        public ?string $h,
        public ?CarbonImmutable $attribStart,
        public ?CarbonImmutable $attribEnd,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            nom: (string) ($data['nom'] ?? ''),
            idReseau: (int) ($data['idReseau'] ?? 0),
            idConfiguration: (int) ($data['idConfiguration'] ?? 0),
            x: isset($data['x']) ? (string) $data['x'] : null,
            y: isset($data['y']) ? (string) $data['y'] : null,
            lat: isset($data['lat']) ? (float) $data['lat'] : null,
            lon: isset($data['lon']) ? (float) $data['lon'] : null,
            altitude: isset($data['altitude']) ? (string) $data['altitude'] : null,
            h: isset($data['h']) ? (string) $data['h'] : null,
            attribStart: self::parseDate($data['attribStart'] ?? null),
            attribEnd: self::parseDate($data['attribEnd'] ?? null),
        );
    }

    public function openStreetMapUrl(): ?string
    {
        if ($this->lat === null || $this->lon === null) {
            return null;
        }

        return "https://www.openstreetmap.org/?mlat={$this->lat}&mlon={$this->lon}";
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'id_reseau' => $this->idReseau,
            'id_configuration' => $this->idConfiguration,
            'x' => $this->x,
            'y' => $this->y,
            'lat' => $this->lat,
            'lon' => $this->lon,
            'altitude' => $this->altitude,
            'h' => $this->h,
            'attrib_start' => $this->attribStart,
            'attrib_end' => $this->attribEnd,
        ];
    }

    /**
     * The API sends "2025-02-03 00:00:00.000000" in its own timezone, but a null or an empty
     * string is common on a station whose attribution is still open.
     */
    private static function parseDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse($value, config('issep.timezone'));
        } catch (Exception) {
            return null;
        }
    }
}
