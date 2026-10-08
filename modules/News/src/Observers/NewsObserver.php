<?php

declare(strict_types=1);

namespace AcMarche\News\Observers;

use AcMarche\News\Models\News;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 *
 * Notifying users about a new news is handled by the NewsNotification listener,
 * triggered by the NewsProcessed event dispatched once the news is created.
 */
final class NewsObserver
{
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        // ...
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(): void
    {
        // ...
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(): void
    {
        // ...
    }

    /**
     * Handle the News "restored" event.
     */
    public function restored(): void
    {
        // ...
    }

    /**
     * Handle the News "forceDeleted" event.
     */
    public function forceDeleted(): void
    {
        // ...
    }
}
