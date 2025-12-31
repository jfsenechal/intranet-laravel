<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use App\Models\User;

describe('IncomingMail Creation From Attachment', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['username' => 'test_user']);
        $this->actingAs($this->user);
    });

    test('can create incoming mail with services and recipients', function () {
        $primaryService = Service::factory()->create(['is_active' => true]);
        $secondaryService = Service::factory()->create(['is_active' => true]);
        $primaryRecipient = Recipient::factory()->create(['is_active' => true]);
        $secondaryRecipient = Recipient::factory()->create(['is_active' => true]);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-2024-002',
            'sender' => 'Test Sender from Email',
            'mail_date' => now(),
            'description' => 'Test email subject as description',
            'is_registered' => true,
            'has_acknowledgment' => false,
            'is_notified' => false,
        ]);

        // Attach primary services
        $mail->services()->attach($primaryService->id, ['is_primary' => true]);

        // Attach secondary services
        $mail->services()->attach($secondaryService->id, ['is_primary' => false]);

        // Attach primary recipients
        $mail->recipients()->attach($primaryRecipient->id, ['is_primary' => true]);

        // Attach secondary recipients
        $mail->recipients()->attach($secondaryRecipient->id, ['is_primary' => false]);

        expect($mail)->toBeInstanceOf(IncomingMail::class)
            ->and($mail->reference_number)->toBe('TEST-2024-002')
            ->and($mail->sender)->toBe('Test Sender from Email')
            ->and($mail->is_registered)->toBeTrue()
            ->and($mail->has_acknowledgment)->toBeFalse()
            ->and($mail->user_add)->toBe('test_user')
            ->and($mail->services)->toHaveCount(2)
            ->and($mail->recipients)->toHaveCount(2)
            ->and($mail->primaryService)->toHaveCount(1)
            ->and($mail->primaryRecipient)->toHaveCount(1);
    });

    test('can create incoming mail with only primary services and recipients', function () {
        $primaryService = Service::factory()->create(['is_active' => true]);
        $primaryRecipient = Recipient::factory()->create(['is_active' => true]);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-2024-003',
            'sender' => 'Another Sender',
            'mail_date' => now(),
            'description' => null,
            'is_registered' => false,
            'has_acknowledgment' => true,
            'is_notified' => false,
        ]);

        $mail->services()->attach($primaryService->id, ['is_primary' => true]);
        $mail->recipients()->attach($primaryRecipient->id, ['is_primary' => true]);

        expect($mail->services)->toHaveCount(1)
            ->and($mail->recipients)->toHaveCount(1)
            ->and($mail->primaryService->first()->id)->toBe($primaryService->id)
            ->and($mail->primaryRecipient->first()->id)->toBe($primaryRecipient->id);
    });

    test('can create incoming mail without services or recipients', function () {
        $mail = IncomingMail::create([
            'reference_number' => 'TEST-2024-004',
            'sender' => 'Minimal Sender',
            'mail_date' => now(),
            'is_registered' => false,
            'has_acknowledgment' => false,
            'is_notified' => false,
        ]);

        expect($mail->services)->toHaveCount(0)
            ->and($mail->recipients)->toHaveCount(0);
    });
});
