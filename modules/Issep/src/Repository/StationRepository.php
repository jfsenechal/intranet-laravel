<?php

declare(strict_types=1);

namespace AcMarche\Issep\Repository;

use AcMarche\Issep\Dto\Indice;
use AcMarche\Issep\Dto\Station;
use AcMarche\Issep\Exceptions\IssepException;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * The ISSEP data turned into the module's own objects.
 *
 * A station and its readings come from two different endpoints, joined on the sensor
 * configuration id: /locations gives the station, /lastbelaqi and /belaqi give the indices
 * of a configuration. Each endpoint is read at most once per request; the client caches
 * beyond that.
 */
final class StationRepository
{
    /**
     * @var array<int, Station>|null
     */
    private ?array $stations = null;

    /**
     * @var array<int, Indice>|null
     */
    private ?array $lastBelAqi = null;

    /**
     * @var array<int, Indice>|null
     */
    private ?array $belAqi = null;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $configs = null;

    public function __construct(private readonly IssepApiClient $client) {}

    /**
     * Every station of the network, keyed by station id and sorted by name.
     *
     * @return array<int, Station>
     *
     * @throws IssepException
     */
    public function stations(): array
    {
        if ($this->stations !== null) {
            return $this->stations;
        }

        $stations = [];

        foreach ($this->client->locations() as $row) {
            if (! is_array($row)) {
                continue;
            }

            $station = Station::fromApi($row);
            $stations[$station->id] = $station;
        }

        uasort($stations, fn (Station $a, Station $b): int => $a->nom <=> $b->nom);

        return $this->stations = $stations;
    }

    /**
     * @throws IssepException
     */
    public function station(int $id): ?Station
    {
        return $this->stations()[$id] ?? null;
    }

    /**
     * The last BelAQI index of every sensor configuration.
     *
     * @return array<int, Indice>
     *
     * @throws IssepException
     */
    public function lastBelAqi(): array
    {
        return $this->lastBelAqi ??= self::toIndices($this->client->lastBelAqi());
    }

    /**
     * The whole BelAQI history the API exposes, newest first.
     *
     * @return array<int, Indice>
     *
     * @throws IssepException
     */
    public function belAqi(): array
    {
        return $this->belAqi ??= self::sortByTimestampDesc(self::toIndices($this->client->belAqi()));
    }

    /**
     * The current index of one station.
     *
     * A station whose own sensor has nothing recent falls back, when asked to, to the index
     * of the fallback network, flagged as fixed so the UI can say so.
     *
     * @throws IssepException
     */
    public function lastBelAqiForStation(int $idConfiguration, bool $withFallback = false): ?Indice
    {
        $indice = self::latest(
            array_filter($this->lastBelAqi(), fn (Indice $indice): bool => $indice->configId === $idConfiguration)
        );

        if ($indice instanceof Indice) {
            return $indice;
        }

        if (! $withFallback) {
            return null;
        }

        $fallbackNetworkId = (int) config('issep.fallback_network_id');

        $fallback = self::latest(
            array_filter($this->lastBelAqi(), fn (Indice $indice): bool => $indice->networkId === $fallbackNetworkId)
        );

        return $fallback?->asFixed();
    }

    /**
     * The BelAQI history of one station, newest first.
     *
     * @return array<int, Indice>
     *
     * @throws IssepException
     */
    public function belAqiForStation(int $idConfiguration, ?CarbonInterface $since = null): array
    {
        $indices = array_filter(
            $this->belAqi(),
            fn (Indice $indice): bool => $indice->configId === $idConfiguration
                && ($since === null || ($indice->ts !== null && $indice->ts->greaterThanOrEqualTo($since))),
        );

        return array_values($indices);
    }

    /**
     * The last raw measurement of one sensor configuration, as the API sends it.
     *
     * The keys are left untouched (the API mixes `id_configuration` with camelCase measurement
     * names) because the configuration page displays whatever the sensor reports.
     *
     * @return array<string, mixed>|null
     *
     * @throws IssepException
     */
    public function config(int $idConfiguration): ?array
    {
        $this->configs ??= array_values(array_filter(
            $this->client->lastData(),
            fn (mixed $row): bool => is_array($row),
        ));

        foreach ($this->configs as $config) {
            if (self::configurationIdOf($config) === $idConfiguration) {
                return $config;
            }
        }

        return null;
    }

    /**
     * The oldest BelAQI reading the API still exposes for a station.
     *
     * /belaqi answers with the last thousand readings of the whole network and takes no date
     * range, so this is where a station's available history begins — a caller asking for a
     * longer window than that cannot be served it.
     *
     * @throws IssepException
     */
    public function oldestBelAqiTimestamp(int $idConfiguration): ?CarbonImmutable
    {
        $oldest = null;

        foreach ($this->belAqiForStation($idConfiguration) as $indice) {
            if (! $indice->ts instanceof CarbonImmutable) {
                continue;
            }

            if (! $oldest instanceof CarbonImmutable || $indice->ts->lessThan($oldest)) {
                $oldest = $indice->ts;
            }
        }

        return $oldest;
    }

    /**
     * The raw measurements of one sensor configuration over a range of days, inclusive.
     *
     * The API treats the end of its range as exclusive — asking for start 2026-08-16 and end
     * 2026-08-17 returns the 16th only — so the day the caller asked for is included by
     * requesting the day after it.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function measurements(int $idConfiguration, CarbonInterface $dateBegin, CarbonInterface $dateEnd): array
    {
        $rows = $this->client->stationData(
            $idConfiguration,
            $dateBegin->format('Y-m-d'),
            $dateEnd->copy()->addDay()->format('Y-m-d'),
        );

        return array_values(array_filter($rows, fn (mixed $row): bool => is_array($row)));
    }

    /**
     * Forget everything read so far, including the cached HTTP responses.
     */
    public function refresh(): void
    {
        $this->stations = null;
        $this->lastBelAqi = null;
        $this->belAqi = null;
        $this->configs = null;

        $this->client->flush();
    }

    /**
     * Both spellings appear across the ISSEP endpoints, so a lookup accepts either.
     *
     * @param  array<string, mixed>  $config
     */
    private static function configurationIdOf(array $config): ?int
    {
        $value = $config['id_configuration'] ?? $config['idConfiguration'] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, Indice>
     */
    private static function toIndices(array $rows): array
    {
        $indices = [];

        foreach ($rows as $row) {
            if (is_array($row)) {
                $indices[] = Indice::fromApi($row);
            }
        }

        return $indices;
    }

    /**
     * @param  array<int, Indice>  $indices
     * @return array<int, Indice>
     */
    private static function sortByTimestampDesc(array $indices): array
    {
        usort($indices, fn (Indice $a, Indice $b): int => ($b->ts?->getTimestamp() ?? 0) <=> ($a->ts?->getTimestamp() ?? 0));

        return $indices;
    }

    /**
     * @param  array<int, Indice>  $indices
     */
    private static function latest(array $indices): ?Indice
    {
        $latest = null;

        foreach ($indices as $indice) {
            if (! $latest instanceof Indice) {
                $latest = $indice;

                continue;
            }

            if (($indice->ts?->getTimestamp() ?? 0) >= ($latest->ts?->getTimestamp() ?? 0)) {
                $latest = $indice;
            }
        }

        return $latest;
    }
}
