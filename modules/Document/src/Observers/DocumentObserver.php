<?php

namespace AcMarche\Document\Observers;

use AcMarche\Document\Models\Document;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 */
final class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void {}

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        // ...
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        // ...
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        // ...
    }

    /**
     * Handle the Document "forceDeleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        // ...
    }
}
