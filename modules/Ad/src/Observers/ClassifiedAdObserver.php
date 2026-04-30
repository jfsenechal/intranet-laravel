<?php

declare(strict_types=1);

namespace AcMarche\Ad\Observers;

use AcMarche\Ad\Mail\ClassifiedAdEmail;
use AcMarche\Ad\Models\ClassifiedAd;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Address;

/**
 * Seel all observers https://laravel.com/docs/12.x/eloquent#events
 */
final class ClassifiedAdObserver
{
    /**
     * Handle the Ad "created" event.
     */
    public function created(ClassifiedAd $classifiedAd): void
    {
        $users = User::query()->get();
        foreach ($users as $user) {
            try {
                Mail::to(new Address('jf@marche.be'))
                    ->send(new ClassifiedAdEmail($classifiedAd));
            } catch (Exception $e) {
                dd($e->getMessage());
            }
        }
    }

    /**
     * Handle the Ad "updated" event.
     */
    public function updated(): void
    {
        // ...
    }

    /**
     * Handle the Ad "deleted" event.
     */
    public function deleted(): void
    {
        // ...
    }

    /**
     * Handle the Ad "restored" event.
     */
    public function restored(): void
    {
        // ...
    }

    /**
     * Handle the Ad "forceDeleted" event.
     */
    public function forceDeleted(): void
    {
        // ...
    }
}
