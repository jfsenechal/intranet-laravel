<?php

declare(strict_types=1);

use AcMarche\Issep\Dto\Indice;
use AcMarche\Issep\Exceptions\IssepException;
use AcMarche\Issep\Repository\IssepApiClient;
use AcMarche\Issep\Repository\StationRepository;
use AcMarche\Issep\Tests\IssepApiFake;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    /*
     * The fixtures build their timestamps relative to now when the HTTP stub answers, while
     * the assertions rebuild them later: without a frozen clock a tick between the two makes
     * the record keys disagree.
     */
    $this->freezeTime();

    IssepApiFake::fake();
});

function stationRepository(): StationRepository
{
    return new StationRepository(IssepApiClient::fromConfig());
}

describe('stations', function (): void {
    it('sorts the stations by name and keys them by id', function (): void {
        $stations = stationRepository()->stations();

        expect(array_keys($stations))->toBe([
            IssepApiFake::STATION_AVENUE_FRANCE,
            IssepApiFake::STATION_WITH_READING,
            IssepApiFake::STATION_WITHOUT_READING,
        ]);
    });

    it('reads a station by id', function (): void {
        expect(stationRepository()->station(IssepApiFake::STATION_WITH_READING)?->nom)
            ->toBe('Chaussée de Liège (1)');
    });

    it('returns nothing for an unknown station', function (): void {
        expect(stationRepository()->station(99999))->toBeNull();
    });

    it('reads the station list once per instance', function (): void {
        $repository = stationRepository();
        $repository->stations();
        $repository->stations();

        Http::assertSentCount(1);
    });
});

describe('last index of a station', function (): void {
    it('takes the index of the station’s own configuration', function (): void {
        $indice = stationRepository()->lastBelAqiForStation(IssepApiFake::CONFIG_WITH_READING);

        expect($indice)->toBeInstanceOf(Indice::class)
            ->and($indice->aqiValue)->toBe(3)
            ->and($indice->isFixed)->toBeFalse();
    });

    it('has no index for a configuration that reports nothing', function (): void {
        expect(stationRepository()->lastBelAqiForStation(IssepApiFake::CONFIG_WITHOUT_READING))->toBeNull();
    });

    /**
     * Asked for a fallback, a configuration without a reading takes the index of the fallback
     * network and is flagged, which is what the stations table shows as "corrigé".
     */
    it('falls back to the network index when asked', function (): void {
        $indice = stationRepository()->lastBelAqiForStation(
            IssepApiFake::CONFIG_WITHOUT_READING,
            withFallback: true,
        );

        expect($indice)->toBeInstanceOf(Indice::class)
            ->and($indice->aqiValue)->toBe(6)
            ->and($indice->isFixed)->toBeTrue();
    });

    it('does not flag a configuration that has its own reading', function (): void {
        $indice = stationRepository()->lastBelAqiForStation(
            IssepApiFake::CONFIG_WITH_READING,
            withFallback: true,
        );

        expect($indice->isFixed)->toBeFalse();
    });
});

describe('index history', function (): void {
    it('returns the readings of one configuration, newest first', function (): void {
        $indices = stationRepository()->belAqiForStation(IssepApiFake::CONFIG_WITH_READING);

        expect($indices)->toHaveCount(4)
            ->and(array_map(fn (Indice $indice): int => $indice->aqiValue, $indices))->toBe([3, 5, 8, 10]);
    });

    it('keeps only the readings after a given moment', function (): void {
        $indices = stationRepository()->belAqiForStation(
            IssepApiFake::CONFIG_WITH_READING,
            now()->subHours(24),
        );

        expect($indices)->toHaveCount(2);
    });

    it('reads the timestamps in the API timezone', function (): void {
        $indice = stationRepository()->lastBelAqiForStation(IssepApiFake::CONFIG_WITH_READING);

        expect($indice->ts->getTimestamp())->toBe(now()->subHour()->getTimestamp());
    });
});

describe('sensor configuration', function (): void {
    it('finds the last measurement of a configuration', function (): void {
        expect(stationRepository()->config(IssepApiFake::CONFIG_WITH_READING))
            ->toHaveKey('bmeT', 21.5);
    });

    /**
     * /lastdata keys the configuration as "idConfiguration", but "id_configuration" appears
     * too, so a lookup accepts either spelling.
     */
    it('finds a measurement keyed with the snake_case spelling', function (): void {
        IssepApiFake::fake([
            'lastdata' => Http::response([
                ['id_configuration' => IssepApiFake::CONFIG_WITH_READING, 'bmeT' => 19.5],
            ]),
        ]);

        expect(stationRepository()->config(IssepApiFake::CONFIG_WITH_READING))
            ->toHaveKey('bmeT', 19.5);
    });

    it('returns nothing for a configuration that has no measurement', function (): void {
        expect(stationRepository()->config(123456))->toBeNull();
    });
});

describe('failures', function (): void {
    it('refuses to call the API without a token', function (): void {
        config()->set('issep.token', null);

        expect(fn (): array => stationRepository()->stations())
            ->toThrow(IssepException::class, "n'est pas configuré");
    });

    it('explains a refused token', function (): void {
        IssepApiFake::fake(['locations' => Http::response(status: 401)]);

        expect(fn (): array => stationRepository()->stations())
            ->toThrow(IssepException::class, 'jeton');
    });

    it('rejects a body that is not a list', function (): void {
        IssepApiFake::fake(['locations' => Http::response('<html>proxy</html>')]);

        expect(fn (): array => stationRepository()->stations())
            ->toThrow(IssepException::class, 'liste');
    });
});

describe('caching', function (): void {
    it('serves a second reader from the cache', function (): void {
        config()->set('issep.cache_ttl', 300);

        stationRepository()->stations();
        stationRepository()->stations();

        Http::assertSentCount(1);
    });

    it('goes back to the API after a refresh', function (): void {
        config()->set('issep.cache_ttl', 300);

        $repository = stationRepository();
        $repository->stations();
        $repository->refresh();
        $repository->stations();

        Http::assertSentCount(2);
    });
});
