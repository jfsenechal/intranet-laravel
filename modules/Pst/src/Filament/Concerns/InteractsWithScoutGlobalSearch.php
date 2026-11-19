<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Concerns;

use Filament\GlobalSearch\GlobalSearchResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Meilisearch\Exceptions\ApiException;

/**
 * Resolves Filament global search results through the model's Scout index.
 *
 * When the Meilisearch index does not exist yet (never imported, or flushed),
 * it falls back to Filament's default database search instead of crashing the
 * global search bar. Run `php artisan pst:meili` to (re)create the indexes.
 */
trait InteractsWithScoutGlobalSearch
{
    /**
     * @return Collection<int, GlobalSearchResult>
     */
    public static function getGlobalSearchResults(string $search): Collection
    {
        try {
            $keys = static::getModel()::search($search)->keys();
        } catch (ApiException $exception) {
            if ($exception->errorCode !== 'index_not_found') {
                throw $exception;
            }

            Log::warning('Scout index missing, falling back to database global search.', [
                'model' => static::getModel(),
                'message' => $exception->getMessage(),
            ]);

            return parent::getGlobalSearchResults($search);
        }

        return static::getGlobalSearchEloquentQuery()
            ->whereKey($keys)
            ->limit(static::$globalSearchResultsLimit)
            ->get()
            ->map(function (Model $record): ?GlobalSearchResult {
                $url = static::getGlobalSearchResultUrl($record);

                if (blank($url)) {
                    return null;
                }

                return new GlobalSearchResult(
                    title: static::getGlobalSearchResultTitle($record),
                    url: $url,
                    details: static::getGlobalSearchResultDetails($record),
                    actions: static::getGlobalSearchResultActions($record),
                );
            })
            ->filter();
    }
}
