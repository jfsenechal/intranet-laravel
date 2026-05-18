<?php

declare(strict_types=1);

use AcMarche\College\Filament\Resources\Notifications\Pages\CreateNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\EditNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\ListNotifications;
use AcMarche\College\Filament\Resources\Notifications\Pages\ViewNotification;
use AcMarche\College\Models\Notification;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('college-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);
});

it('renders list, create, view and edit pages', function (): void {
    $notification = Notification::factory()->create();

    livewire(ListNotifications::class)->assertOk();
    livewire(CreateNotification::class)->assertOk();
    livewire(ViewNotification::class, ['record' => $notification->id])->assertOk();
    livewire(EditNotification::class, ['record' => $notification->id])->assertOk();
});

it('lists notifications', function (): void {
    $notifications = Notification::factory(3)->create();

    livewire(ListNotifications::class)
        ->loadTable()
        ->assertCanSeeTableRecords($notifications);
});

it('creates a notification via the form', function (): void {
    livewire(CreateNotification::class)
        ->fillForm([
            'file_name' => 'convocation.pdf',
            'mime' => 'application/pdf',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Notification::class, [
        'file_name' => 'convocation.pdf',
        'mime' => 'application/pdf',
    ]);
});

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListNotifications::class)->assertForbidden();
});
