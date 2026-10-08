<?php

declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushServiceProvider as BaseWebPushServiceProvider;

/**
 * Rebinds Minishlink\WebPush so it is built with a PSR-3 logger.
 *
 * Without a logger the library reports unmet requirements — such as the optional
 * GMP/BCMath extensions — through trigger_error(). Laravel turns those notices
 * into an ErrorException, so a purely informational advisory aborts whatever is
 * sending the notification. With a logger they are written to the log instead.
 *
 * The package provider is still auto-discovered; this one extends it and is
 * registered in bootstrap/providers.php, so it boots afterwards and its binding
 * wins. Extending also keeps webPushAuth(), webPushClient() and webPushConfig()
 * — the VAPID and HTTP client wiring — owned upstream.
 */
final class WebPushServiceProvider extends BaseWebPushServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $config = $this->webPushConfig();

        $this->app->when(WebPushChannel::class)
            ->needs(WebPush::class)
            ->give(fn (): WebPush => (new WebPush(
                auth: $this->webPushAuth(),
                client: $this->webPushClient($config['client_options']),
                requestFactory: new HttpFactory,
                streamFactory: new HttpFactory,
                logger: Log::channel(),
            ))
                ->setReuseVAPIDHeaders(true)
                ->setAutomaticPadding($config['automatic_padding']));
    }
}
