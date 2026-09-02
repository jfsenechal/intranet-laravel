<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Pages\AiDemoVideo;
use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $this->actingAs(User::factory()->create());
});

it('shows the recording to any user of the panel', function (): void {
    expect(AiDemoVideo::canAccess())->toBeTrue();

    $this->get(AiDemoVideo::getUrl(panel: 'courrier-panel'))
        ->assertOk()
        ->assertSee(asset(AiDemoVideo::VIDEO_PATH), escape: false);
});

it('points at a recording that ships with the application', function (): void {
    expect(public_path(AiDemoVideo::VIDEO_PATH))->toBeReadableFile();
});
