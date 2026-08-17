<?php

declare(strict_types=1);

namespace AcMarche\Issep\Repository;

use AcMarche\Issep\Exceptions\IssepException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * The raw ISSEP open data endpoints, decoded but not interpreted.
 *
 * Every response is cached for issep.cache_ttl seconds: a Filament table re-runs its
 * records() function on every Livewire interaction, so without this a single sort would
 * cost another round trip to the API.
 */
final class IssepApiClient
{
    private const CACHE_PREFIX = 'issep:';

    public function __construct(
        private readonly string $baseUri,
        private readonly ?string $token,
        private readonly int $timeout,
        private readonly int $cacheTtl,
        private readonly bool $verifySsl,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            baseUri: mb_rtrim((string) config('issep.uri'), '/'),
            token: config('issep.token'),
            timeout: (int) config('issep.timeout', 30),
            cacheTtl: (int) config('issep.cache_ttl', 0),
            verifySsl: (bool) config('issep.verify_ssl', true),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->baseUri) && filled($this->token);
    }

    /**
     * The stations of the network.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function locations(): array
    {
        return $this->get($this->baseUri.'/locations');
    }

    /**
     * The last measurement of every sensor configuration of the network.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function lastData(): array
    {
        return $this->get($this->baseUri.'/lastdata');
    }

    /**
     * The last BelAQI index of every sensor configuration of the network.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function lastBelAqi(): array
    {
        return $this->get($this->baseUri.'/lastbelaqi');
    }

    /**
     * The BelAQI history of every sensor configuration of the network.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function belAqi(): array
    {
        return $this->get($this->baseUri.'/belaqi');
    }

    /**
     * The raw measurements of one sensor configuration over a date range.
     *
     * This endpoint sits one level above the network in the API, hence the trimmed base URI.
     *
     * @param  string  $dateBegin  Y-m-d
     * @param  string  $dateEnd  Y-m-d
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    public function stationData(int $idConfiguration, string $dateBegin, string $dateEnd): array
    {
        return $this->get(
            $this->measurementBaseUri()."/config/{$idConfiguration}/start/{$dateBegin}/end/{$dateEnd}"
        );
    }

    /**
     * Drop the cached network responses, so the next read hits the API again.
     */
    public function flush(): void
    {
        foreach (['/locations', '/lastdata', '/lastbelaqi', '/belaqi'] as $endpoint) {
            Cache::forget($this->cacheKey($this->baseUri.$endpoint));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    private function get(string $url): array
    {
        if (! $this->isConfigured()) {
            throw new IssepException("L'accès à l'API ISSEP n'est pas configuré (ISSEP_BASE_URI et ISSEP_TOKEN).");
        }

        if ($this->cacheTtl <= 0) {
            return $this->fetch($url);
        }

        return Cache::remember(
            $this->cacheKey($url),
            $this->cacheTtl,
            fn (): array => $this->fetch($url),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws IssepException
     */
    private function fetch(string $url): array
    {
        try {
            $response = Http::withToken((string) $this->token)
                ->withOptions(['verify' => $this->verifySsl])
                ->acceptJson()
                ->timeout($this->timeout)
                ->get($url);
        } catch (ConnectionException $connectionException) {
            throw new IssepException(
                "Impossible de joindre l'API ISSEP: {$connectionException->getMessage()}",
                previous: $connectionException,
            );
        }

        if ($response->unauthorized() || $response->forbidden()) {
            throw new IssepException(
                "L'API ISSEP a refusé le jeton d'accès (HTTP {$response->status()}). Il est probablement expiré."
            );
        }

        if ($response->failed()) {
            throw new IssepException("L'API ISSEP a répondu HTTP {$response->status()}.");
        }

        try {
            $data = $response->json();
        } catch (Throwable $throwable) {
            throw new IssepException(
                "La réponse de l'API ISSEP n'est pas du JSON valide: {$throwable->getMessage()}",
                previous: $throwable,
            );
        }

        if (! is_array($data)) {
            throw new IssepException("La réponse de l'API ISSEP n'est pas une liste de données.");
        }

        return $data;
    }

    private function cacheKey(string $url): string
    {
        return self::CACHE_PREFIX.sha1($url);
    }

    private function measurementBaseUri(): string
    {
        return Str::beforeLast($this->baseUri, '/');
    }
}
