<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\DashboardPage;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
});

it('lists recent mail the user is a recipient of', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $mine = IncomingMail::factory()->create();
    $mine->recipients()->attach($recipient->id);

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers->pluck('id'))->toContain($mine->id);
});

it('lists recent mail linked to a service the user belongs to', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $service = Service::factory()->create();
    $recipient->services()->attach($service->id);

    $mine = IncomingMail::factory()->create();
    $mine->services()->attach($service->id);

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers->pluck('id'))->toContain($mine->id);
});

it('lists mail regardless of age', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $old = IncomingMail::factory()->create(['created_at' => now()->subYear()]);
    $old->recipients()->attach($recipient->id);

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers->pluck('id'))->toContain($old->id);
});

it('excludes mail the user is not linked to', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    Recipient::factory()->create(['username' => $user->username]);
    $other = IncomingMail::factory()->create();

    $myCourriers = livewire(DashboardPage::class)->instance()->myCourriers;

    expect($myCourriers->pluck('id'))->not->toContain($other->id);
});
