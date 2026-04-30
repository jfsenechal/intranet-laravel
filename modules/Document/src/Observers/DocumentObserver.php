<?php

declare(strict_types=1);

namespace AcMarche\Document\Observers;

use AcMarche\Document\Models\Document;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 */
final class DocumentObserver
{
    /**
     * Handle the Document "updated" event.
     */
    public function updated(): void
    {
        // ...
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(): void
    {
        // ...
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(): void
    {
        // ...
    }

    /**
     * Handle the Document "forceDeleted" event.
     */
    public function forceDeleted(): void
    {
        // ...
    }
}
