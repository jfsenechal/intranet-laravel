<?php

declare(strict_types=1);

namespace AcMarche\News\Observers;

use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 */
final class NewsObserver
{
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        $users = User::query()->whereNotNull('email')->get();
        foreach ($users as $user) {
            try {
                Mail::to($user->email)
                    ->send(new NewsEmail($news));
            } catch (Exception $e) {
                Log::error('Failed to send news notification email', [
                    'news_id' => $news->id,
                    'user_id' => $user->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
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
