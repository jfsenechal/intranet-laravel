<?php

declare(strict_types=1);

return [
    /*
     * The ISSEP open data API for the Marche microsensor network. The base URI ends with the
     * network name (".../microsensor/marche"); the raw measurement endpoint lives one level
     * up, which IssepApiClient handles.
     */
    'uri' => env('ISSEP_BASE_URI', 'https://opendata.issep.be/env/air/api/microsensor/marche'),
    'token' => env('ISSEP_TOKEN'),

    'timeout' => (int) env('ISSEP_TIMEOUT', 30),

    /*
     * The timezone the API expresses its timestamps in. It sends them without an offset
     * ("2026-08-17T07:38:38"), so it has to be stated rather than guessed.
     *
     * Measured against the live API: /lastdata reported its freshest measurement at 07:38:38
     * when it was 07:47 UTC (09:47 in Brussels), on a feed that reports about every minute.
     * Belgian local time would have made that reading over two hours old, so the API is UTC.
     * Filament renders it in app.display_timezone, which puts it back on the Belgian clock.
     */
    'timezone' => env('ISSEP_TIMEZONE', 'UTC'),

    /*
     * The legacy intranet had to skip peer verification to reach this host from inside the
     * network, where an intercepting proxy re-signs the certificate. Kept on by default, and
     * flip ISSEP_VERIFY_SSL to false if the request fails on the certificate chain.
     */
    'verify_ssl' => (bool) env('ISSEP_VERIFY_SSL', true),

    /*
     * Every Livewire interaction on the stations table (sort, search, paginate) re-runs the
     * records() function, so the API responses are cached for this many seconds to keep one
     * page of browsing down to a single round trip per endpoint. The sensors report roughly
     * hourly, so a few minutes of staleness is invisible. Set to 0 to always hit the API.
     */
    'cache_ttl' => (int) env('ISSEP_CACHE_TTL', 300),

    /*
     * Stations whose own sensor has no recent BelAQI reading fall back to the indice of this
     * network (Sinsin), which is what the legacy intranet displayed as "corrigé". The Sinsin
     * reading arrives on /lastbelaqi with a null configId, so its network is the only thing
     * it can be matched on.
     */
    'fallback_network_id' => (int) env('ISSEP_FALLBACK_NETWORK_ID', 10),

    /*
     * How many days of raw measurements the search page may ask for at once. The sensors
     * report about once a minute, so one day is roughly 1800 rows and close to a megabyte of
     * JSON, which then sits in the cache (the database store here) to keep paging through the
     * results off the API. A wider range is clamped to this rather than refused.
     */
    'max_range_days' => (int) env('ISSEP_MAX_RANGE_DAYS', 7),
];
