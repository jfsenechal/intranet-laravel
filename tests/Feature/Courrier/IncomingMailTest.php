<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

RefreshDatabaseState::$migrated = true;

// uses(RefreshDatabase::class);
pest()->use(DatabaseTruncation::class);
/*
beforeEach(function () {
    dump(13);
    uses(DatabaseTruncation::class);
});*/

describe('IncomingMail Model', function () {
    test('can create an incoming mail', function () {
        $mail = IncomingMail::factory()->create([
            'reference_number' => 'TEST-2024-001',
            'sender' => 'Test Sender',
            'description' => 'Test Description',
        ]);
        User::factory()->create([

        ]);
        expect($mail)->toBeInstanceOf(IncomingMail::class)
            ->and($mail->reference_number)->toBe('TEST-2024-001')
            ->and($mail->sender)->toBe('Test Sender')
            ->and($mail->description)->toBe('Test Description');
    });

    test('has correct default boolean values', function () {
        $mail = IncomingMail::factory()->create();

        expect($mail->is_notified)->toBeBool()
            ->and($mail->is_registered)->toBeBool()
            ->and($mail->has_acknowledgment)->toBeBool();
    });

    test('can create with notified state', function () {
        $mail = IncomingMail::factory()->notified()->create();

        expect($mail->is_notified)->toBeTrue();
    });

    test('can create with registered state', function () {
        $mail = IncomingMail::factory()->registered()->create();

        expect($mail->is_registered)->toBeTrue();
    });

    test('can create with acknowledgment state', function () {
        $mail = IncomingMail::factory()->withAcknowledgment()->create();

        expect($mail->has_acknowledgment)->toBeTrue();
    });

    test('casts date correctly', function () {
        $mail = IncomingMail::factory()->create([
            'mail_date' => '2024-01-15',
        ]);

        expect($mail->mail_date)->toBeInstanceOf(Illuminate\Support\Carbon::class);
    });

    test('soft deletes work correctly', function () {
        $mail = IncomingMail::factory()->create();
        $mailId = $mail->id;

        $mail->delete();

        expect(IncomingMail::find($mailId))->toBeNull()
            ->and(IncomingMail::withTrashed()->find($mailId))->not->toBeNull();
    });
});

describe('IncomingMail Relationships', function () {
    test('can attach services to incoming mail', function () {
        $mail = IncomingMail::factory()->create();
        $service = Service::factory()->create();

        $mail->services()->attach($service->id, ['is_primary' => true]);

        expect($mail->services)->toHaveCount(1)
            ->and($mail->services->first()->id)->toBe($service->id)
            ->and($mail->services->first()->pivot->is_primary)->toBeTrue();
    });

    test('can attach recipients to incoming mail', function () {
        $mail = IncomingMail::factory()->create();
        $recipient = Recipient::factory()->create();

        $mail->recipients()->attach($recipient->id, ['is_primary' => false]);

        expect($mail->recipients)->toHaveCount(1)
            ->and($mail->recipients->first()->id)->toBe($recipient->id)
            ->and($mail->recipients->first()->pivot->is_primary)->toBeFalse();
    });

    test('can get primary service', function () {
        $mail = IncomingMail::factory()->create();
        $primaryService = Service::factory()->create();
        $secondaryService = Service::factory()->create();

        $mail->services()->attach($primaryService->id, ['is_primary' => true]);
        $mail->services()->attach($secondaryService->id, ['is_primary' => false]);

        expect($mail->primaryService)->toHaveCount(1)
            ->and($mail->primaryService->first()->id)->toBe($primaryService->id);
    });

    test('can get primary recipient', function () {
        $mail = IncomingMail::factory()->create();
        $primaryRecipient = Recipient::factory()->create();
        $secondaryRecipient = Recipient::factory()->create();

        $mail->recipients()->attach($primaryRecipient->id, ['is_primary' => true]);
        $mail->recipients()->attach($secondaryRecipient->id, ['is_primary' => false]);

        expect($mail->primaryRecipient)->toHaveCount(1)
            ->and($mail->primaryRecipient->first()->id)->toBe($primaryRecipient->id);
    });
});

describe('Service Model', function () {
    test('can create a service', function () {
        $service = Service::factory()->create([
            'name' => 'Service Travaux',
            'initials' => 'ST',
        ]);

        expect($service)->toBeInstanceOf(Service::class)
            ->and($service->name)->toBe('Service Travaux')
            ->and($service->initials)->toBe('ST')
            ->and($service->is_active)->toBeTrue();
    });

    test('generates slug automatically', function () {
        $service = Service::factory()->create([
            'name' => 'Cabinet du Bourgmestre',
            'slug' => null,
        ]);

        expect($service->slug)->toBe('cabinet-du-bourgmestre');
    });

    test('can create inactive service', function () {
        $service = Service::factory()->inactive()->create();

        expect($service->is_active)->toBeFalse();
    });
});

describe('Recipient Model', function () {
    test('can create a recipient', function () {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@test.com',
        ]);

        expect($recipient)->toBeInstanceOf(Recipient::class)
            ->and($recipient->first_name)->toBe('Jean')
            ->and($recipient->last_name)->toBe('Dupont')
            ->and($recipient->email)->toBe('jean.dupont@test.com')
            ->and($recipient->is_active)->toBeTrue();
    });

    test('generates slug automatically', function () {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'slug' => null,
        ]);

        expect($recipient->slug)->toBe('dupont_jean');
    });

    test('has full name accessor', function () {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        expect($recipient->full_name)->toBe('Jean Dupont');
    });

    test('can have a supervisor', function () {
        $supervisor = Recipient::factory()->create();
        $recipient = Recipient::factory()->create([
            'supervisor_id' => $supervisor->id,
        ]);

        expect($recipient->supervisor)->toBeInstanceOf(Recipient::class)
            ->and($recipient->supervisor->id)->toBe($supervisor->id);
    });

    test('can have subordinates', function () {
        $supervisor = Recipient::factory()->create();
        $subordinate1 = Recipient::factory()->create(['supervisor_id' => $supervisor->id]);
        $subordinate2 = Recipient::factory()->create(['supervisor_id' => $supervisor->id]);

        expect($supervisor->subordinates)->toHaveCount(2);
    });

    test('can create inactive recipient', function () {
        $recipient = Recipient::factory()->inactive()->create();

        expect($recipient->is_active)->toBeFalse();
    });

    test('can create recipient who receives attachments', function () {
        $recipient = Recipient::factory()->receivesAttachments()->create();

        expect($recipient->receives_attachments)->toBeTrue();
    });
});
