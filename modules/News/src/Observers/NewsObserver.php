<?php

namespace AcMarche\News\Observers;

use AcMarche\News\Mail\NewsEmail;
use AcMarche\News\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 */
class NewsObserver
{
    /**
     * Handle the News "created" event.
     */
    public function created(News $news): void
    {
        $users = User::query()->get();
        foreach ($users as $user) {
            try {
                Mail::to(new Address('jf@marche.be'))
                    ->send(new NewsEmail($news));
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    /**
     * Handle the News "updated" event.
     */
    public function updated(News $news): void
    {
        // ...
    }

    /**
     * Handle the News "deleted" event.
     */
    public function deleted(News $news): void
    {
        // ...
    }

    /**
     * Handle the News "restored" event.
     */
    public function restored(News $news): void
    {
        // ...
    }

    /**
     * Handle the News "forceDeleted" event.
     */
    public function forceDeleted(News $news): void
    {
        // ...
    }
}
