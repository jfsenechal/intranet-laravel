<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Override model connection for testing
    config(['database.connections.maria-courrier' => config('database.connections.sqlite')]);
});

describe('IncomingMail Model', function () {
    test('can create an incoming mail', function () {
        $mail = IncomingMail::factory()->create([
            'reference' => 'TEST-2024-001',
            'sender_name' => 'Test Sender',
            'subject' => 'Test Subject',
        ]);

        expect($mail)->toBeInstanceOf(IncomingMail::class)
            ->and($mail->reference)->toBe('TEST-2024-001')
            ->and($mail->sender_name)->toBe('Test Sender')
            ->and($mail->subject)->toBe('Test Subject')
            ->and($mail->status)->not->toBeNull();
    });

    test('has correct default status', function () {
        $mail = IncomingMail::factory()->create();

        expect($mail->status)->toBeIn(['pending', 'processed', 'archived']);
    });

    test('can create with pending status', function () {
        $mail = IncomingMail::factory()->pending()->create();

        expect($mail->status)->toBe('pending')
            ->and($mail->processed_date)->toBeNull();
    });

    test('can create with processed status', function () {
        $mail = IncomingMail::factory()->processed()->create();

        expect($mail->status)->toBe('processed')
            ->and($mail->processed_date)->not->toBeNull();
    });

    test('can create with archived status', function () {
        $mail = IncomingMail::factory()->archived()->create();

        expect($mail->status)->toBe('archived');
    });

    test('can create with attachment', function () {
        $mail = IncomingMail::factory()->withAttachment()->create();

        expect($mail->attachment_path)->not->toBeNull()
            ->and($mail->attachment_name)->not->toBeNull()
            ->and($mail->attachment_size)->not->toBeNull()
            ->and($mail->attachment_mime)->not->toBeNull();
    });

    test('reference must be unique', function () {
        IncomingMail::factory()->create(['reference' => 'UNIQUE-001']);

        expect(fn () => IncomingMail::factory()->create(['reference' => 'UNIQUE-001']))
            ->toThrow(Illuminate\Database\QueryException::class);
    });

    test('casts dates correctly', function () {
        $mail = IncomingMail::factory()->create([
            'received_date' => '2024-01-15',
            'processed_date' => '2024-01-20',
        ]);

        expect($mail->received_date)->toBeInstanceOf(Illuminate\Support\Carbon::class)
            ->and($mail->processed_date)->toBeInstanceOf(Illuminate\Support\Carbon::class);
    });

    test('soft deletes work correctly', function () {
        $mail = IncomingMail::factory()->create();
        $mailId = $mail->id;

        $mail->delete();

        expect(IncomingMail::find($mailId))->toBeNull()
            ->and(IncomingMail::withTrashed()->find($mailId))->not->toBeNull();
    });
});
