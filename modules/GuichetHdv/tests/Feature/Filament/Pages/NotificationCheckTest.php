<?php

declare(strict_types=1);

use AcMarche\GuichetHdv\Filament\Pages\NotificationCheck;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('guichet-hdv-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('can render the notification check page', function (): void {
    livewire(NotificationCheck::class)
        ->assertOk()
        ->assertSee('État des notifications');
});

it('stores the browser push subscription from the check page', function (): void {
    $endpoint = 'https://push.example.com/sub/check-page';

    livewire(NotificationCheck::class)
        ->call('storePushSubscription', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'public-key', 'auth' => 'auth-token'],
        ]);

    $this->assertDatabaseHas('push_subscriptions', [
        'subscribable_id' => auth()->id(),
        'endpoint' => $endpoint,
    ]);
});
