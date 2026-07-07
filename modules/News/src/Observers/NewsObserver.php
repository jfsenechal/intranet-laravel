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

    /**
     * Resolve the users who should be notified about the given news.
     *
     * @return Collection<int, User>
     */
    private function recipientsFor(News $news): Collection
    {
        $department = $this->departmentValue($news);

        $query = User::query()->whereNotNull('email');

        if ($department !== DepartmentEnum::COMMON->value) {
            $query->whereJsonContains('departments', $department);
        }

        return $query->get();
    }

    /**
     * Normalize the news department to its enum string value, tolerating both a
     * DepartmentEnum instance (as set by the Filament form) and a raw string.
     */
    private function departmentValue(News $news): string
    {
        if ($news->department instanceof DepartmentEnum) {
            return $news->department->value;
        }

        return mb_strtoupper((string) $news->department);
    }
}
