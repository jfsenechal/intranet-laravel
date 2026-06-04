<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Concerns;

use Illuminate\Support\Facades\Auth;

trait InteractsWithWebPush
{
    /**
     * Persist the browser's Web Push subscription for the current user.
     *
     * @param  array{endpoint?: string, keys?: array{p256dh?: string, auth?: string}}  $subscription
     */
    public function storePushSubscription(array $subscription): void
    {
        $endpoint = $subscription['endpoint'] ?? null;

        if ($endpoint === null) {
            return;
        }

        Auth::user()?->updatePushSubscription(
            $endpoint,
            $subscription['keys']['p256dh'] ?? null,
            $subscription['keys']['auth'] ?? null,
        );
    }
}
