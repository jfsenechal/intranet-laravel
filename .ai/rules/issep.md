---
paths:
  - 'modules/Issep/**'
---

# Issep

## ISSEP API timestamps are UTC, sent without an offset
The endpoints send `"2026-08-17T07:38:38"` with no offset. Measured against the live API, it is UTC: /lastdata's freshest measurement was 07:38:38 at 07:47 UTC (09:47 Brussels) on a feed that reports every minute. Parse with `config('issep.timezone')` (see Dto\Indice, Dto\Station) rather than assuming; Filament renders in app.display_timezone. Note the legacy Symfony intranet displayed these raw, so it showed every reading two hours early.

## The endpoints disagree on field names
The same measurement is `ppbno`/`mwhBat` in /lastdata but `ppbNo`/`mWhBat` in /config/{id}/start/…; the configuration id is `idConfiguration` in /locations and /lastdata but `id_configuration` in older payloads. Never key on one spelling: `MeasurementLabels::describe()` normalises (case and underscores dropped) and `StationRepository::configurationIdOf()` accepts either.

## The Sinsin row arrives without a configId
In /lastbelaqi and /belaqi, the RTM - Sinsin reading has `configId: null` and only `networkId: 10`, so it cannot be joined to station 23 by configuration. That is what `issep.fallback_network_id` and the `withFallback` flag exist for — not a general "nearest station" rule.

## No local storage
Every row is read live from the API. `config/database.php` is an empty `['connections' => []]` stub only because ModuleServiceProviderTrait::registerDatabaseConnection() requires the file for every module.
