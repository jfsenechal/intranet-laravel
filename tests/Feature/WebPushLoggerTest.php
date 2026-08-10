<?php

declare(strict_types=1);

use Minishlink\WebPush\WebPush;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushServiceProvider as PackageWebPushServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Resolve the WebPush instance the notification channel is built with.
 */
function resolveChannelWebPush(): WebPush
{
    $channel = app(WebPushChannel::class);

    $property = new ReflectionProperty($channel, 'webPush');

    return $property->getValue($channel);
}

it('boots after the auto-discovered package provider so our binding wins', function (): void {
    $loaded = array_keys(app()->getLoadedProviders());

    // Both are registered; ours is loaded from bootstrap/providers.php, which
    // Laravel boots after the package-manifest providers.
    expect($loaded)
        ->toContain(App\Providers\WebPushServiceProvider::class)
        ->toContain(PackageWebPushServiceProvider::class);
});

it('builds WebPush with a PSR-3 logger so requirement advisories are logged, not thrown', function (): void {
    $logger = new ReflectionProperty(WebPush::class, 'logger');

    expect($logger->getValue(resolveChannelWebPush()))->toBeInstanceOf(LoggerInterface::class);
});

it('resolves the channel without turning a PHP notice into an ErrorException', function (): void {
    // Without a logger, Minishlink\WebPush reports missing GMP/BCMath through
    // trigger_error(), which Laravel escalates to ErrorException at this point.
    expect(fn (): WebPush => resolveChannelWebPush())->not->toThrow(ErrorException::class);
});

it('keeps the padding and VAPID header settings the package provider applied', function (): void {
    $webPush = resolveChannelWebPush();

    $reuseVapidHeaders = new ReflectionProperty($webPush, 'reuseVAPIDHeaders');
    $automaticPadding = new ReflectionProperty($webPush, 'automaticPadding');

    expect($reuseVapidHeaders->getValue($webPush))->toBeTrue()
        ->and($automaticPadding->getValue($webPush))->not->toBe(0);
});
