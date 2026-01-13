<?php

declare(strict_types=1);

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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

describe('NotifyRecipients Page Access', function () {
    test('admin user can access notify recipients page', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertSuccessful();
    });

    test('user with ROLE_INDICATEUR_VILLE_ADMIN can access notify recipients page', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->addRole($role);

        $this->actingAs($user)
            ->get(NotifyRecipients::getUrl())
            ->assertSuccessful();
    });

    test('regular user cannot access notify recipients page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(NotifyRecipients::getUrl())
            ->assertForbidden();
    });

    test('guest cannot access notify recipients page', function () {
        $this->get(NotifyRecipients::getUrl())
            ->assertRedirect();
    });
});

describe('NotifyRecipients Page Display', function () {
    test('notify recipients page displays correct title', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertSee('Notifier les destinataires');
    });

    test('notify recipients page shows incoming mails for selected date', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        $mail = IncomingMail::factory()->create([
            'reference_number' => 'TEST-2024-001',
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertSee('TEST-2024-001');
    });

    test('notify recipients page does not show already notified mails', function () {
        $admin = User::factory()->create(['is_administrator' => true]);

        IncomingMail::factory()->create([
            'reference_number' => 'NOTIFIED-001',
            'mail_date' => now(),
            'is_notified' => true,
        ]);

        $this->actingAs($admin)
            ->get(NotifyRecipients::getUrl())
            ->assertDontSee('NOTIFIED-001');
    });
});

describe('SendIncomingMailNotificationJob', function () {
    test('job dispatches mail to active recipients', function () {
        Mail::fake();
        Queue::fake();

        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'test@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        SendIncomingMailNotificationJob::dispatch(now());

        Queue::assertPushed(SendIncomingMailNotificationJob::class);
    });

    test('job does not send to inactive recipients', function () {
        Mail::fake();

        $recipient = Recipient::factory()->inactive()->create([
            'email' => 'inactive@example.com',
        ]);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertNothingQueued();
    });

    test('job does not send to recipients without email', function () {
        Mail::fake();

        Recipient::factory()->create([
            'is_active' => true,
            'email' => null,
        ]);

        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertNothingQueued();
    });

    test('recipient with index role receives all mails', function () {
        Mail::fake();

        $user = User::factory()->create(['username' => 'indexuser']);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
        $user->addRole($role);

        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'index@example.com',
            'username' => 'indexuser',
        ]);

        // Create mail that is not directly assigned to the recipient
        IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertQueued(IncomingMailNotification::class, function ($mail) use ($recipient) {
            return $mail->hasTo($recipient->email) && $mail->incomingMails->count() === 1;
        });
    });

    test('regular recipient only receives mails where they are assigned', function () {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'regular@example.com',
        ]);

        $otherRecipient = Recipient::factory()->create([
            'is_active' => true,
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

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertQueued(IncomingMailNotification::class, function ($mail) use ($recipient) {
            return $mail->hasTo($recipient->email) && $mail->incomingMails->count() === 1;
        });
    });

    test('recipient receives mails through service membership', function () {
        Mail::fake();

        $service = Service::factory()->create();
        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'service@example.com',
        ]);
        $recipient->services()->attach($service->id);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->services()->attach($service->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertQueued(IncomingMailNotification::class, function ($mailable) use ($recipient) {
            return $mailable->hasTo($recipient->email);
        });
    });

    test('mail is marked as notified after sending', function () {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'test@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        expect($mail->fresh()->is_notified)->toBeTrue();
    });

    test('attachments are included when recipient has receives_attachments flag', function () {
        Mail::fake();

        $recipient = Recipient::factory()->receivesAttachments()->create([
            'is_active' => true,
            'email' => 'attachments@example.com',
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertQueued(IncomingMailNotification::class, function ($mailable) {
            return $mailable->includeAttachments === true;
        });
    });

    test('attachments are not included when recipient does not have receives_attachments flag', function () {
        Mail::fake();

        $recipient = Recipient::factory()->create([
            'is_active' => true,
            'email' => 'noattachments@example.com',
            'receives_attachments' => false,
        ]);

        $mail = IncomingMail::factory()->create([
            'mail_date' => now(),
            'is_notified' => false,
        ]);
        $mail->recipients()->attach($recipient->id, ['is_primary' => true]);

        $job = new SendIncomingMailNotificationJob(now());
        $job->handle();

        Mail::assertQueued(IncomingMailNotification::class, function ($mailable) {
            return $mailable->includeAttachments === false;
        });
    });
});

describe('IncomingMailNotification Mailable', function () {
    test('mailable has correct subject', function () {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails);

        expect($mailable->envelope()->subject)->toBe('Notification de courriers entrants');
    });

    test('mailable uses correct view', function () {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails);

        expect($mailable->content()->html)->toBe('courrier::mail.incoming-mail-notification');
    });

    test('mailable returns empty attachments when includeAttachments is false', function () {
        $recipient = Recipient::factory()->create();
        $mails = collect([IncomingMail::factory()->create()]);

        $mailable = new IncomingMailNotification($recipient, $mails, false);

        expect($mailable->attachments())->toBeEmpty();
    });
});
