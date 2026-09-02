<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Filament\Pages\GuestDemoVideo;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('meal-delivery-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('shows the recording to a user of the panel', function (): void {
    expect(GuestDemoVideo::canAccess())->toBeTrue();

    $this->get(GuestDemoVideo::getUrl(panel: 'meal-delivery-panel'))
        ->assertOk()
        ->assertSee(asset(GuestDemoVideo::VIDEO_PATH), escape: false);
});

it('points at a recording that ships with the application', function (): void {
    expect(public_path(GuestDemoVideo::VIDEO_PATH))->toBeReadableFile();
});

it('keeps the page out of reach of a user without the meal delivery role', function (): void {
    $this->actingAs(User::factory()->create());

    expect(GuestDemoVideo::canAccess())->toBeFalse();
});
