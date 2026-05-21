<?php

declare(strict_types=1);

use AcMarche\College\Enums\NotificationType;
use AcMarche\College\Filament\Resources\Notifications\Pages\CreateNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\EditNotification;
use AcMarche\College\Filament\Resources\Notifications\Pages\ListNotifications;
use AcMarche\College\Filament\Resources\Notifications\Pages\ViewNotification;
use AcMarche\College\Mail\NotificationMail;
use AcMarche\College\Models\Notification;
use AcMarche\College\Models\Recipient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('college-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);
});

/**
 * @param  array<string, bool>  $flags
 */
function makeRecipient(array $flags = []): Recipient
{
    return Recipient::query()->forceCreate(array_merge([
        'slugname' => Str::random(12),
        'last_name' => fake()->lastName(),
        'first_name' => fake()->firstName(),
        'email' => fake()->unique()->safeEmail(),
        'pv_service' => false,
        'ordre_service' => false,
        'ordre_college' => false,
        'pv_college' => false,
    ], $flags));
}

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

it('sends an ordre notification only to recipients flagged for the uploaded document', function (): void {
    Mail::fake();
    Storage::fake('local');

    $collegeRecipient = makeRecipient(['ordre_college' => true]);
    $serviceOnlyRecipient = makeRecipient(['ordre_service' => true]);
    $unrelatedRecipient = makeRecipient(['pv_college' => true]);

    livewire(CreateNotification::class)
        ->fillForm([
            'type' => NotificationType::Ordre->value,
            'date_college' => '2026-05-21',
            'sujet' => 'Convocation du Collège',
            'message' => '<p>Bonjour</p>',
            'file_college' => UploadedFile::fake()->create('oj-college.pdf', 50, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    // Only the recipient flagged "ordre_college" gets a mail: the college file
    // was the only one uploaded, so the service-only recipient is skipped.
    Mail::assertQueued(NotificationMail::class, 1);
    Mail::assertQueued(
        fn (NotificationMail $mail): bool => $mail->hasTo($collegeRecipient->email),
    );
    Mail::assertNotQueued(
        fn (NotificationMail $mail): bool => $mail->hasTo($serviceOnlyRecipient->email)
            || $mail->hasTo($unrelatedRecipient->email),
    );

    assertDatabaseHas(Notification::class, [
        'file_name' => 'oj-college.pdf',
        'mime' => 'application/pdf',
    ]);
});

it('attaches both documents when a recipient is flagged for college and service', function (): void {
    Mail::fake();
    Storage::fake('local');

    $bothRecipient = makeRecipient(['pv_college' => true, 'pv_service' => true]);

    livewire(CreateNotification::class)
        ->fillForm([
            'type' => NotificationType::Pv->value,
            'date_college' => '2026-05-21',
            'sujet' => 'PV du Collège',
            'message' => '<p>Voir documents</p>',
            'file_college' => UploadedFile::fake()->create('pv-college.pdf', 50, 'application/pdf'),
            'file_service' => UploadedFile::fake()->create('pv-service.pdf', 50, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    Mail::assertQueued(
        fn (NotificationMail $mail): bool => $mail->hasTo($bothRecipient->email)
            && count($mail->files) === 2,
    );
});

it('requires at least one document', function (): void {
    livewire(CreateNotification::class)
        ->fillForm([
            'type' => NotificationType::Ordre->value,
            'date_college' => '2026-05-21',
            'sujet' => 'Sans document',
            'message' => '<p>Rien</p>',
        ])
        ->call('create')
        ->assertHasFormErrors(['file_college', 'file_service']);
});

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListNotifications::class)->assertForbidden();
});
