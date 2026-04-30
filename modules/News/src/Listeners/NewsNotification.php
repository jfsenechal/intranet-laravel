<?php

declare(strict_types=1);

namespace AcMarche\News\Listeners;

use AcMarche\News\Models\News;

final class NewsNotification
{
    public function handle(): void
    {
        // $this->sendMail($event->news());
    }
}
