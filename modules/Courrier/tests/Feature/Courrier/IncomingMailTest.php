<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

describe('IncomingMail Model', function (): void {
    test('can create an incoming mail', function (): void {
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

    test('has correct default boolean values', function (): void {
        $mail = IncomingMail::factory()->create();

        expect($mail->is_notified)->toBeBool()
            ->and($mail->is_registered)->toBeBool()
            ->and($mail->has_acknowledgment)->toBeBool();
    });

    test('can create with notified state', function (): void {
        $mail = IncomingMail::factory()->notified()->create();

        expect($mail->is_notified)->toBeTrue();
    });

    test('can create with registered state', function (): void {
        $mail = IncomingMail::factory()->registered()->create();

        expect($mail->is_registered)->toBeTrue();
    });

    test('can create with acknowledgment state', function (): void {
        $mail = IncomingMail::factory()->withAcknowledgment()->create();

        expect($mail->has_acknowledgment)->toBeTrue();
    });

    test('casts date correctly', function (): void {
        $mail = IncomingMail::factory()->create([
            'mail_date' => '2024-01-15',
        ]);

        expect($mail->mail_date)->toBeInstanceOf(CarbonImmutable::class);
    });

    test('assigns first reference number for cpas department', function (): void {
        $mail = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        expect($mail->reference_number)->toBe('1');
    });

    test('increments reference number for cpas department, ignoring any provided value', function (): void {
        $first = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
            'reference_number' => '5',
        ]);

        $second = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        expect($first->reference_number)->toBe('1')
            ->and($second->reference_number)->toBe('2');
    });

    test('orders cpas reference numbers numerically not lexicographically', function (): void {
        foreach (range(1, 9) as $number) {
            IncomingMail::factory()->create([
                'department' => DepartmentCourrierEnum::CPAS->value,
            ]);
        }

        $mail = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        expect($mail->reference_number)->toBe('10');
    });

    test('ignores non-numeric legacy references when computing the next cpas number', function (): void {
        // Legacy rows carry values such as "-20180316" that wrap to a near
        // UINT64 value under CAST(... AS UNSIGNED). Bypass the creating hook so
        // the bogus value is persisted as-is.
        IncomingMail::withoutEvents(function (): void {
            IncomingMail::factory()->create([
                'department' => DepartmentCourrierEnum::CPAS->value,
                'reference_number' => '-20180316',
            ]);
            IncomingMail::factory()->create([
                'department' => DepartmentCourrierEnum::CPAS->value,
                'reference_number' => '42',
            ]);
        });

        $mail = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        expect($mail->reference_number)->toBe('43');
    });

    test('does not auto-assign reference number for non-cpas departments', function (): void {
        $mail = IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::VILLE->value,
            'reference_number' => 'VILLE-2024-001',
        ]);

        expect($mail->reference_number)->toBe('VILLE-2024-001');
    });

    test('soft deletes work correctly', function (): void {
        $mail = IncomingMail::factory()->create();
        $mailId = $mail->id;

        $mail->delete();

        expect(IncomingMail::find($mailId))->toBeNull()
            ->and(IncomingMail::withTrashed()->find($mailId))->not->toBeNull();
    });
});

describe('IncomingMail Relationships', function (): void {
    test('can attach services to incoming mail', function (): void {
        $mail = IncomingMail::factory()->create();
        $service = Service::factory()->create();

        $mail->services()->attach($service->id, ['is_primary' => true]);

        expect($mail->services)->toHaveCount(1)
            ->and($mail->services->first()->id)->toBe($service->id)
            ->and($mail->services->first()->pivot->is_primary)->toBeTrue();
    });

    test('can attach recipients to incoming mail', function (): void {
        $mail = IncomingMail::factory()->create();
        $recipient = Recipient::factory()->create();

        $mail->recipients()->attach($recipient->id, ['is_primary' => false]);

        expect($mail->recipients)->toHaveCount(1)
            ->and($mail->recipients->first()->id)->toBe($recipient->id)
            ->and($mail->recipients->first()->pivot->is_primary)->toBeFalse();
    });

    test('can get primary service', function (): void {
        $mail = IncomingMail::factory()->create();
        $primaryService = Service::factory()->create();
        $secondaryService = Service::factory()->create();

        $mail->services()->attach($primaryService->id, ['is_primary' => true]);
        $mail->services()->attach($secondaryService->id, ['is_primary' => false]);

        expect($mail->primaryService)->toHaveCount(1)
            ->and($mail->primaryService->first()->id)->toBe($primaryService->id);
    });

    test('can get primary recipient', function (): void {
        $mail = IncomingMail::factory()->create();
        $primaryRecipient = Recipient::factory()->create();
        $secondaryRecipient = Recipient::factory()->create();

        $mail->recipients()->attach($primaryRecipient->id, ['is_primary' => true]);
        $mail->recipients()->attach($secondaryRecipient->id, ['is_primary' => false]);

        expect($mail->primaryRecipient)->toHaveCount(1)
            ->and($mail->primaryRecipient->first()->id)->toBe($primaryRecipient->id);
    });
});

describe('Service Model', function (): void {
    test('can create a service', function (): void {
        $service = Service::factory()->create([
            'name' => 'Service Travaux',
            'initials' => 'ST',
        ]);

        expect($service)->toBeInstanceOf(Service::class)
            ->and($service->name)->toBe('Service Travaux')
            ->and($service->initials)->toBe('ST');
    });

    test('generates slug automatically', function (): void {
        $service = Service::factory()->create([
            'name' => 'Cabinet du Bourgmestre',
            'slugname' => null,
        ]);

        expect($service->slugname)->toBe('cabinet-du-bourgmestre');
    });
});

describe('Recipient Model', function (): void {
    test('can create a recipient', function (): void {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@test.com',
        ]);

        expect($recipient)->toBeInstanceOf(Recipient::class)
            ->and($recipient->first_name)->toBe('Jean')
            ->and($recipient->last_name)->toBe('Dupont')
            ->and($recipient->email)->toBe('jean.dupont@test.com');
    });

    test('generates slug automatically', function (): void {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'slug' => null,
        ]);

        expect($recipient->slug)->toBe('dupont-jean');
    });

    test('has full name accessor', function (): void {
        $recipient = Recipient::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
        ]);

        expect($recipient->full_name)->toBe('Jean Dupont');
    });

    test('can have a supervisor', function (): void {
        $supervisor = Recipient::factory()->create();
        $recipient = Recipient::factory()->create([
            'supervisor_id' => $supervisor->id,
        ]);

        expect($recipient->supervisor)->toBeInstanceOf(Recipient::class)
            ->and($recipient->supervisor->id)->toBe($supervisor->id);
    });

    test('can have subordinates', function (): void {
        $supervisor = Recipient::factory()->create();
        $subordinate1 = Recipient::factory()->create(['supervisor_id' => $supervisor->id]);
        $subordinate2 = Recipient::factory()->create(['supervisor_id' => $supervisor->id]);

        expect($supervisor->subordinates)->toHaveCount(2);
    });

    test('can create recipient who receives attachments', function (): void {
        $recipient = Recipient::factory()->receivesAttachments()->create();

        expect($recipient->receives_attachments)->toBeTrue();
    });
});
