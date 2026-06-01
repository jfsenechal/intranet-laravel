<?php

declare(strict_types=1);

namespace AcMarche\Ad\Observers;

use AcMarche\Ad\Mail\ClassifiedAdEmail;
use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Ad\Models\Subscriber;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
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
        foreach (User::query()->cursor() as $user) {
            try {
                Mail::to(new Address(
                    (string) $user->email,
                    mb_trim($user->first_name.' '.$user->last_name),
                ))->send(new ClassifiedAdEmail($classifiedAd));
            } catch (Exception $exception) {
                Log::warning('ClassifiedAd user mail failed', [
                    'user_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        foreach (Subscriber::query()->cursor() as $subscriber) {
            try {
                Mail::to(new Address(
                    (string) $subscriber->email,
                    mb_trim($subscriber->first_name.' '.$subscriber->last_name),
                ))->send(new ClassifiedAdEmail($classifiedAd));
            } catch (Exception $exception) {
                Log::warning('ClassifiedAd subscriber mail failed', [
                    'subscriber_id' => $subscriber->id,
                    'error' => $exception->getMessage(),
                ]);
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
