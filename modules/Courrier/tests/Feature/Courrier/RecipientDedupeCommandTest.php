<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('merges duplicate recipients onto the lowest id and repoints references', function (): void {
    $canonical = Recipient::factory()->create([
        'username' => 'jdupont',
        'email' => null,
        'receives_attachments' => false,
    ]);
    $duplicate = Recipient::factory()->create([
        'username' => 'jdupont',
        'email' => 'jean.dupont@marche.be',
        'receives_attachments' => true,
    ]);

    $mail = IncomingMail::factory()->create();
    $service = Service::factory()->create();

    // The mail and service are linked to the duplicate, not the canonical.
    $duplicate->incomingMails()->attach($mail, ['is_primary' => true]);
    $duplicate->services()->attach($service);

    // A subordinate points at the duplicate as its supervisor.
    $subordinate = Recipient::factory()->create([
        'username' => 'msmith',
        'supervisor_id' => $duplicate->id,
    ]);

    $this->artisan('courrier:recipients:dedupe')->assertSuccessful();

    // Only the canonical recipient for that username survives.
    expect(Recipient::where('username', 'jdupont')->count())->toBe(1);
    expect(Recipient::find($duplicate->id))->toBeNull();

    $canonical->refresh();
    expect($canonical->email)->toBe('jean.dupont@marche.be')
        ->and($canonical->receives_attachments)->toBeTrue();

    // References now point at the canonical recipient.
    expect($canonical->incomingMails()->where('incoming_mails.id', $mail->id)->exists())->toBeTrue()
        ->and($canonical->services()->where('courrier_services.id', $service->id)->exists())->toBeTrue()
        ->and($subordinate->fresh()->supervisor_id)->toBe($canonical->id);
});

it('collapses conflicting pivot rows without creating duplicates', function (): void {
    $canonical = Recipient::factory()->create(['username' => 'apilote']);
    $duplicate = Recipient::factory()->create(['username' => 'apilote']);

    $mail = IncomingMail::factory()->create();
    $service = Service::factory()->create();

    // Both recipients already share the same mail and service.
    $canonical->incomingMails()->attach($mail, ['is_primary' => false]);
    $duplicate->incomingMails()->attach($mail, ['is_primary' => true]);
    $canonical->services()->attach($service);
    $duplicate->services()->attach($service);

    $this->artisan('courrier:recipients:dedupe')->assertSuccessful();

    expect(DB::connection('maria-courrier')->table('incoming_mail_recipient')
        ->where('incoming_mail_id', $mail->id)->count())->toBe(1)
        ->and(DB::connection('maria-courrier')->table('recipient_service')
            ->where('service_id', $service->id)->count())->toBe(1);

    // The primary flag from the duplicate is preserved on the surviving row.
    expect(DB::connection('maria-courrier')->table('incoming_mail_recipient')
        ->where('incoming_mail_id', $mail->id)
        ->where('recipient_id', $canonical->id)
        ->value('is_primary'))->toBeTruthy();
});

it('adds a unique index on username and blocks further duplicates', function (): void {
    Recipient::factory()->create(['username' => 'zzz']);

    $this->artisan('courrier:recipients:dedupe')->assertSuccessful();

    $hasUnique = collect(Schema::connection('maria-courrier')->getIndexes('recipients'))
        ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['username']);

    expect($hasUnique)->toBeTrue();

    expect(fn () => Recipient::factory()->create(['username' => 'zzz']))
        ->toThrow(Illuminate\Database\UniqueConstraintViolationException::class);
});

it('reports duplicates without changing data in dry-run mode', function (): void {
    Recipient::factory()->create(['username' => 'dryrun']);
    Recipient::factory()->create(['username' => 'dryrun']);

    $this->artisan('courrier:recipients:dedupe --dry-run')->assertSuccessful();

    expect(Recipient::where('username', 'dryrun')->count())->toBe(2);

    $hasUnique = collect(Schema::connection('maria-courrier')->getIndexes('recipients'))
        ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['username']);

    expect($hasUnique)->toBeFalse();
});
