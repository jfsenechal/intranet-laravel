<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushServiceProvider as BaseWebPushServiceProvider;

/**
 * Replaces the package provider (see the "dont-discover" entry in composer.json)
 * so that Minishlink\WebPush is built with a PSR-3 logger.
 *
 * Without a logger the library reports unmet requirements — such as the optional
 * GMP/BCMath extensions — through trigger_error(). Laravel turns those notices
 * into an ErrorException, so a purely informational advisory aborts the request
 * that sends the notification. With a logger they are written to the log instead.
 */
final class WebPushServiceProvider extends BaseWebPushServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $this->app->when(WebPushChannel::class)
            ->needs(WebPush::class)
            ->give(fn (): WebPush => (new WebPush(
                auth: $this->webPushAuth(),
                timeout: 30,
                clientOptions: config('webpush.client_options', []),
                logger: Log::channel(),
            ))
                ->setReuseVAPIDHeaders(true)
                ->setAutomaticPadding(config('webpush.automatic_padding')));
    }
}
