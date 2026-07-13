<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\NotifyRecipients;
use AcMarche\Courrier\Jobs\SendIncomingMailNotificationJob;
use AcMarche\Courrier\Mail\IncomingMailNotification;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

describe('NotifyRecipients Page Access', function (): void {
    test('admin user can access notify recipients page', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertSuccessful();
    });

    test('user with ROLE_INDICATEUR_VILLE_ADMIN can access notify recipients page', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->addRole($role);

        $this->actingAs($user)
            ->get(NotifyRecipients::getUrl())
            ->assertSuccessful();
    });

    test('an admin without a department cannot send notifications', function (): void {
        Queue::fake();

        // is_administrator holds no courrier admin role, so currentAdminDepartment() is null.
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin);

        livewire(NotifyRecipients::class)
            ->callAction('sendNotifications')
            ->assertNotified('Envoi impossible');

        Queue::assertNotPushed(SendIncomingMailNotificationJob::class);
    });

    test('a department admin can send notifications', function (): void {
        Queue::fake();

        $villeAdmin = User::factory()->create();
        $villeAdmin->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]));
        $this->actingAs($villeAdmin);

        livewire(NotifyRecipients::class)
            ->callAction('sendNotifications');

        Queue::assertPushed(SendIncomingMailNotificationJob::class);
    });

    test('regular user cannot access notify recipients page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(NotifyRecipients::getUrl())
            ->assertForbidden();
    });

    test('guest cannot access notify recipients page', function (): void {
        $this->get(NotifyRecipients::getUrl())
            ->assertForbidden();
    });
});

describe('NotifyRecipients Page Display', function (): void {
    test('notify recipients page displays correct title', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertSee('Notifier les destinataires');
    });

    test('notify recipients page shows incoming mails for selected date', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $mail = IncomingMail::factory()->create([
            'reference_number' => 'TEST-2024-001',
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $this->actingAs($admin);

        livewire(NotifyRecipients::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$mail]);
    });

    test('notify recipients page does not show already notified mails', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $notifiedMail = IncomingMail::factory()->create([
            'reference_number' => 'NOTIFIED-001',
            'mail_date' => now(),
            'is_notified' => true,
        ]);

        $this->actingAs($admin);

        livewire(NotifyRecipients::class)
            ->loadTable()
            ->assertCanNotSeeTableRecords([$notifiedMail]);
    });

    test('the page shows a button to refresh the list', function (): void {
        $this->actingAs(User::factory()->create(['is_administrator' => true]));

        livewire(NotifyRecipients::class)
            ->assertSee('Rafraichir la liste');
    });

    test('the preview table hides already notified mail unless force_notify is set', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $notifiedMail = IncomingMail::factory()->create([
            'reference_number' => 'FORCE-PREVIEW-001',
            'mail_date' => now(),
            'is_notified' => true,
        ]);

        $this->actingAs($admin);

        // Default preview: already notified mail is excluded.
        livewire(NotifyRecipients::class)
            ->loadTable()
            ->assertCanNotSeeTableRecords([$notifiedMail]);

        // Forced preview: already notified mail is included.
        livewire(NotifyRecipients::class)
            ->set('force_notify', true)
            ->loadTable()
            ->assertCanSeeTableRecords([$notifiedMail]);
    });

    test('send notifications confirmation modal opens without error', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $recipient = Recipient::factory()->create([
            'email' => 'preview@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $this->actingAs($admin);

        livewire(NotifyRecipients::class)
            ->mountAction('sendNotifications')
            ->assertActionMounted('sendNotifications')
            ->assertHasNoErrors();
    });

    test('changing the date filter refreshes the table without error', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $recipient = Recipient::factory()->create([
            'email' => 'reload@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $this->actingAs($admin);

        livewire(NotifyRecipients::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$mail])
            ->set('mail_date', now()->subDay()->format('Y-m-d'))
            ->loadTable()
            ->assertHasNoErrors()
            ->assertCanNotSeeTableRecords([$mail]);
    });
});

describe('SendIncomingMailNotificationJob', function (): void {
    test('job dispatches mail to recipients', function (): void {
        Mail::fake();
        Queue::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'test@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        dispatch(new SendIncomingMailNotificationJob(Date::now()));

        Queue::assertPushed(SendIncomingMailNotificationJob::class);
    });

    test('job does not send to recipients without email', function (): void {
        Mail::fake();

        Recipient::factory()->create([
            'email' => null,
        ]);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertNothingSent();
    });

    test('recipient with index role receives all mails in their department', function (): void {
        Mail::fake();

        $user = User::factory()->create(['username' => 'indexuser']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
        $user->addRole($role);

        $recipient = Recipient::factory()->create([
            'email' => 'index@example.com',
            'username' => 'indexuser',
        ]);

        // Mail in the recipient's department that is not directly assigned to them.
        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mail): bool => $mail->hasTo($recipient->email) && $mail->incomingMails->count() === 1);
    });

    test('every index recipient in a department receives the mail, not only the first', function (): void {
        Mail::fake();

        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);

        $firstUser = User::factory()->create(['username' => 'firstindex']);
        $firstUser->addRole($role);
        $firstRecipient = Recipient::factory()->create([
            'email' => 'first-index@example.com',
            'username' => 'firstindex',
        ]);

        $secondUser = User::factory()->create(['username' => 'secondindex']);
        $secondUser->addRole($role);
        $secondRecipient = Recipient::factory()->create([
            'email' => 'second-index@example.com',
            'username' => 'secondindex',
        ]);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mail): bool => $mail->hasTo($firstRecipient->email));
        Mail::assertSent(IncomingMailNotification::class, fn ($mail): bool => $mail->hasTo($secondRecipient->email));
    });

    test('index recipient only receives mail from their viewable department', function (): void {
        Mail::fake();

        $user = User::factory()->create(['username' => 'villeindex']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
        $user->addRole($role);

        $recipient = Recipient::factory()->create([
            'email' => 'ville-index@example.com',
            'username' => 'villeindex',
        ]);

        $villeMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);

        // Mail from other departments must not reach a Ville index recipient.
        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::BGM->value,
        ]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mail): bool => $mail->hasTo($recipient->email)
                && $mail->incomingMails->count() === 1
                && $mail->incomingMails->first()->is($villeMail)
        );
    });

    test('regular recipient only receives mails where they are assigned', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'regular@example.com',
        ]);

        $otherRecipient = Recipient::factory()->create([
            'email' => 'other@example.com',
        ]);

        // Mail assigned to recipient
        $assignedMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $assignedMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        // Mail not assigned to recipient
        $unassignedMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $unassignedMail->recipients()->attach($otherRecipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mail): bool => $mail->hasTo($recipient->email) && $mail->incomingMails->count() === 1);
    });

    test('recipient receives mails through service membership', function (): void {
        Mail::fake();

        $service = Service::factory()->create();
        $recipient = Recipient::factory()->create([
            'email' => 'service@example.com',
        ]);
        $recipient->services()->attach($service->id);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->services()->attach($service->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mailable) => $mailable->hasTo($recipient->email));
    });

    test('a department admin only notifies mail of their own department', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create(['email' => 'dept@example.com']);

        $cpasMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);
        $cpasMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $villeMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);
        $villeMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        // Triggered by a Cpas admin: only the Cpas mail must be notified.
        $job = new SendIncomingMailNotificationJob(Date::now(), department: DepartmentCourrierEnum::CPAS);
        $job->handle();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mailable): bool => $mailable->hasTo($recipient->email)
                && $mailable->incomingMails->count() === 1
                && $mailable->incomingMails->first()->is($cpasMail)
        );

        expect($cpasMail->fresh()->is_notified)->toBeTrue()
            ->and($villeMail->fresh()->is_notified)->toBeFalse();
    });

    test('force only resets mail of the triggering department', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create(['email' => 'force-dept@example.com']);

        $cpasMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => true,
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);
        $cpasMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $villeMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => true,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);
        $villeMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now(), force: true, department: DepartmentCourrierEnum::CPAS);
        $job->handle();

        // The Ville mail keeps its notified flag: the Cpas admin never touched it.
        expect($cpasMail->fresh()->is_notified)->toBeTrue()
            ->and($villeMail->fresh()->is_notified)->toBeTrue();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mailable): bool => $mailable->incomingMails->count() === 1
                && $mailable->incomingMails->first()->is($cpasMail)
        );
    });

    test('a global administrator notifies mail of every department', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create(['email' => 'global@example.com']);

        $cpasMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);
        $cpasMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $villeMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::VILLE->value,
        ]);
        $villeMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        // No department passed: a global admin notifies everything.
        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mailable): bool => $mailable->hasTo($recipient->email)
                && $mailable->incomingMails->count() === 2
        );
    });

    test('force re-notifies mail that was already notified', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'force@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => true,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now(), force: true);
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mailable): bool => $mailable->hasTo($recipient->email));
        expect($mail->fresh()->is_notified)->toBeTrue();
    });

    test('without force, already notified mail is not re-sent', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'noforce@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => true,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertNothingSent();
    });

    test('the triggering admin is used as the sender when provided', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'sender@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $sender = new Illuminate\Mail\Mailables\Address('admin@example.com', 'Acting Admin');

        $job = new SendIncomingMailNotificationJob(Date::now(), sender: $sender);
        $job->handle();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mailable): bool => $mailable->hasFrom('admin@example.com', 'Acting Admin')
        );
    });

    test('mail falls back to the configured sender when no admin is provided', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'fallback@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(
            IncomingMailNotification::class,
            fn ($mailable): bool => $mailable->hasFrom(config('mail.from.address'))
        );
    });

    test('mail is marked as notified after sending', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'test@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        expect($mail->fresh()->is_notified)->toBeTrue();
    });

    test('attachments are included when recipient has receives_attachments flag', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->receivesAttachments()->create([
            'email' => 'attachments@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mailable): bool => $mailable->includeAttachments === true);
    });

    test('attachments are not included when recipient does not have receives_attachments flag', function (): void {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'email' => 'noattachments@example.com',
            'receives_attachments' => false,
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(Date::now());
        $job->handle();

        Mail::assertSent(IncomingMailNotification::class, fn ($mailable): bool => $mailable->includeAttachments === false);
    });
});

describe('NotifyRecipients preview count', function (): void {
    test('count excludes already notified mail by default and includes it when forced', function (): void {
        $admin = User::factory()->create(['is_administrator' => true]);

        $recipient = Recipient::factory()->create([
            'email' => 'count@example.com',
        ]);

        $notifiedMail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => true,
        ]);
        $notifiedMail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $this->actingAs($admin);

        $component = livewire(NotifyRecipients::class)->instance();

        expect($component->countRecipientsToNotify(false))->toBe(0)
            ->and($component->countRecipientsToNotify(true))->toBe(1);
    });

    test('a Ville admin does not count recipients whose only mail is from another department', function (): void {
        $villeAdmin = User::factory()->create();
        $villeAdmin->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]));

        // A Cpas index recipient with only Cpas mail: visible to them, but the
        // Ville admin must not count them.
        $cpasIndexUser = User::factory()->create(['username' => 'cpasindex']);
        $cpasIndexUser->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value]));
        Recipient::factory()->create(['email' => 'cpas-index@example.com', 'username' => 'cpasindex']);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        $this->actingAs($villeAdmin);

        $component = livewire(NotifyRecipients::class)->instance();

        expect($component->countRecipientsToNotify(false))->toBe(0);
    });
});

describe('IncomingMailNotification Mailable', function (): void {
    test('mailable has correct subject', function (): void {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails);

        expect($mailable->envelope()->subject)->toBe('[Indicateur] Notification de courriers entrants');
    });

    test('mailable subject includes the mail date when provided', function (): void {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails, false, Date::parse('2026-03-15'));

        expect($mailable->envelope()->subject)->toBe('[Indicateur] Notification de courriers entrants du 15/03/2026');
    });

    test('mailable uses correct view', function (): void {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails);

        expect($mailable->content()->html)->toBe('courrier::mail.incoming-mail-notification');
    });

    test('mailable links each courrier description to its view page', function (): void {
        $recipient = Recipient::factory()->create();
        $mail = IncomingMail::factory()->create();
        $mails = collect([$mail]);

        $mailable = new IncomingMailNotification($recipient, $mails);

        $url = route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $mail->id]);

        $mailable->assertSeeInHtml('href="'.$url.'"', false);
    });

    test('mailable returns empty attachments when includeAttachments is false', function (): void {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails, false);

        expect($mailable->attachments())->toBeEmpty();
    });
});
