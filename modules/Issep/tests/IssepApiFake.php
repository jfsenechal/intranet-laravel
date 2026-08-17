<?php

declare(strict_types=1);

namespace AcMarche\Issep\Tests;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * The ISSEP endpoints, faked with payloads captured from the live API.
 *
 * The shapes matter and are not obvious, so they are reproduced faithfully: ISO-8601
 * timestamps without an offset expressed in UTC, integer ids, camelCase measurement fields
 * spelled differently between /lastdata and /config/{id}/start/…, and the RTM - Sinsin
 * reading arriving with a null configId that can only be matched on its network.
 *
 * Two stations of interest: "Chaussée de Liège (1)" (config 10621), which reports normally,
 * and "RTM - Sinsin (43N093)" (config 23), whose index carries no configId.
 */
final class IssepApiFake
{
    public const BASE_URI = 'https://issep.test/env/air/api/microsensor/marche';

    /**
     * The timezone the API writes its timestamps in.
     */
    public const TIMEZONE = 'UTC';

    /**
     * The timezone Filament renders them in, from app.display_timezone.
     */
    public const DISPLAY_TIMEZONE = 'Europe/Brussels';

    public const STATION_WITH_READING = 15;

    public const STATION_WITHOUT_READING = 23;

    public const CONFIG_WITH_READING = 10621;

    public const CONFIG_WITHOUT_READING = 23;

    /**
     * A third station, so sorting and searching have something to separate.
     */
    public const STATION_AVENUE_FRANCE = 19;

    public const CONFIG_AVENUE_FRANCE = 10601;

    public const FALLBACK_NETWORK_ID = 10;

    /**
     * @var array<string, mixed>
     */
    private static array $overrides = [];

    /**
     * Point the module at the fake host and answer every endpoint.
     *
     * @param  array<string, mixed>  $overrides  Endpoint name ("locations", "lastbelaqi",
     *                                           "belaqi", "lastdata", "config") to a Http::response().
     */
    public static function fake(array $overrides = []): void
    {
        config()->set('issep.uri', self::BASE_URI);
        config()->set('issep.token', 'fake-token');
        config()->set('issep.cache_ttl', 0);
        config()->set('issep.fallback_network_id', self::FALLBACK_NETWORK_ID);
        config()->set('issep.timezone', self::TIMEZONE);

        /*
         * One stub dispatching on the endpoint, reading the overrides at request time. A map
         * of url patterns would not do: Http::fake() appends stubs and the first match wins,
         * so a test calling this again to override one endpoint would keep the default answer.
         */
        self::$overrides = $overrides;

        Http::fake(fn (Request $request): PromiseInterface => self::respond($request));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function locations(): array
    {
        return [
            [
                'id' => self::STATION_WITH_READING,
                'idReseau' => 7,
                'idConfiguration' => self::CONFIG_WITH_READING,
                'attribStart' => '2025-01-17T00:00:00',
                'attribEnd' => '2026-12-31T00:00:00',
                'nom' => 'Chaussée de Liège (1)',
                'x' => 219999,
                'y' => 102059,
                'lat' => 50.226024,
                'lon' => 5.348465,
                'altitude' => null,
                'h' => null,
                'userId' => 7,
            ],
            [
                'id' => self::STATION_WITHOUT_READING,
                'idReseau' => self::FALLBACK_NETWORK_ID,
                'idConfiguration' => self::CONFIG_WITHOUT_READING,
                'attribStart' => '2025-01-17T00:00:00',
                'attribEnd' => '2026-12-31T00:00:00',
                'nom' => 'RTM - Sinsin (43N093)',
                'x' => 215500,
                'y' => 95500,
                'lat' => 50.2301,
                'lon' => 5.3502,
                'altitude' => null,
                'h' => null,
                'userId' => 7,
            ],
            [
                'id' => self::STATION_AVENUE_FRANCE,
                'idReseau' => 7,
                'idConfiguration' => self::CONFIG_AVENUE_FRANCE,
                'attribStart' => '2025-01-17T00:00:00',
                'attribEnd' => '2026-12-31T00:00:00',
                'nom' => 'Avenue de France (12)',
                'x' => 218000,
                'y' => 101000,
                'lat' => 50.2276,
                'lon' => 5.3441,
                'altitude' => null,
                'h' => null,
                'userId' => 7,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function lastBelAqi(): array
    {
        return [
            [
                'ts' => self::apiTime(Carbon::now()->subHour()),
                'configId' => self::CONFIG_WITH_READING,
                'aqiValue' => 3,
                'networkId' => 12,
                'pointName' => 'Chaussée de Liège (1)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
            [
                'ts' => self::apiTime(Carbon::now()->subHour()),
                'configId' => self::CONFIG_AVENUE_FRANCE,
                'aqiValue' => 1,
                'networkId' => 12,
                'pointName' => 'Avenue de France (12)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
            /*
             * Sinsin, the row the fallback exists for: no configId at all, only a network.
             */
            [
                'ts' => self::apiTime(Carbon::now()->subMinutes(30)),
                'configId' => null,
                'aqiValue' => 6,
                'networkId' => self::FALLBACK_NETWORK_ID,
                'pointName' => 'RTM - Sinsin (43N093)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
        ];
    }

    /**
     * Readings for the station that reports, spread so each window of the period filter keeps
     * a different number of them: two inside 24 hours, a third inside 3 days, a fourth that
     * only the whole history reaches. Nothing sits on a boundary.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function belAqi(): array
    {
        return [
            [
                'ts' => self::apiTime(Carbon::now()->subHour()),
                'configId' => self::CONFIG_WITH_READING,
                'aqiValue' => 3,
                'networkId' => 12,
                'pointName' => 'Chaussée de Liège (1)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
            [
                'ts' => self::apiTime(Carbon::now()->subHours(5)),
                'configId' => self::CONFIG_WITH_READING,
                'aqiValue' => 5,
                'networkId' => 12,
                'pointName' => 'Chaussée de Liège (1)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
            [
                'ts' => self::apiTime(Carbon::now()->subDays(2)),
                'configId' => self::CONFIG_WITH_READING,
                'aqiValue' => 8,
                'networkId' => 12,
                'pointName' => 'Chaussée de Liège (1)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
            [
                'ts' => self::apiTime(Carbon::now()->subDays(5)),
                'configId' => self::CONFIG_WITH_READING,
                'aqiValue' => 10,
                'networkId' => 12,
                'pointName' => 'Chaussée de Liège (1)',
                'virtualNetworkId' => 7,
                'userId' => 7,
            ],
        ];
    }

    /**
     * The last measurement of every configuration. This endpoint spells the fields with a
     * lowercase element name ("ppbno", "mwhBat") and uses -9999 as a no-value marker.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function lastData(): array
    {
        return [
            [
                'idConfiguration' => self::CONFIG_WITH_READING,
                'moment' => self::apiTime(Carbon::now()->subMinutes(9)),
                'co' => null,
                'ppbno' => 1.828,
                'ppbnoStatut' => 110,
                'ugpcmno2' => -9999,
                'bmeT' => 21.5,
                'pm25' => 9,
                'pm10' => 15,
                'vbat' => 12.8,
                'mwhBat' => 3.2,
            ],
            [
                'idConfiguration' => self::CONFIG_WITHOUT_READING,
                'moment' => self::apiTime(Carbon::now()->subMinutes(21)),
                'co' => null,
                'ppbno' => 0.98,
                'ppbnoStatut' => 110,
                'ugpcmno2' => -9999,
                'bmeT' => 20.1,
                'pm25' => 6.2,
                'pm10' => 11,
                'vbat' => 12.6,
                'mwhBat' => 3.1,
            ],
        ];
    }

    /**
     * The raw measurements of one configuration over a date range. This endpoint spells the
     * fields with a capitalised element name ("ppbNo", "mWhBat"), unlike /lastdata.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function stationData(): array
    {
        return [
            [
                'idConfiguration' => self::CONFIG_WITH_READING,
                'moment' => self::apiTime(Carbon::now()->subHours(2)),
                'co' => null,
                'no' => 7.11,
                'no2' => 4,
                'ppbNo' => 1.828,
                'ppbNoStatut' => 110,
                'ugpcmNo2' => 0,
                'bmeT' => 21.73,
                'bmePres' => 63239.7,
                'bmeRh' => 59.61,
                'pm1' => 14.8,
                'pm25' => 10.236,
                'pm10' => 15.1,
                'vbat' => 12.77,
                'mWhBat' => 3.2,
            ],
            [
                'idConfiguration' => self::CONFIG_WITH_READING,
                'moment' => self::apiTime(Carbon::now()->subHour()),
                'co' => null,
                'no' => 6.02,
                'no2' => 3,
                'ppbNo' => 1.402,
                'ppbNoStatut' => 110,
                'ugpcmNo2' => 0,
                'bmeT' => 22.4,
                'bmePres' => 63240.1,
                'bmeRh' => 57.2,
                'pm1' => 12.1,
                'pm25' => 9.874,
                'pm10' => 13.6,
                'vbat' => 12.81,
                'mWhBat' => 3.3,
            ],
        ];
    }

    /**
     * A moment as the API writes it: ISO-8601 in UTC, with no offset.
     */
    public static function apiTime(Carbon $moment): string
    {
        return $moment->copy()->setTimezone(self::TIMEZONE)->format('Y-m-d\TH:i:s');
    }

    private static function respond(Request $request): PromiseInterface
    {
        $path = (string) parse_url($request->url(), PHP_URL_PATH);
        $endpoint = str_contains($path, '/config/') ? 'config' : basename($path);

        if (isset(self::$overrides[$endpoint])) {
            return self::$overrides[$endpoint];
        }

        return match ($endpoint) {
            'locations' => Http::response(self::locations()),
            'lastbelaqi' => Http::response(self::lastBelAqi()),
            'belaqi' => Http::response(self::belAqi()),
            'lastdata' => Http::response(self::lastData()),
            'config' => Http::response(self::stationData()),
            default => Http::response(status: 404),
        };
    }
}
