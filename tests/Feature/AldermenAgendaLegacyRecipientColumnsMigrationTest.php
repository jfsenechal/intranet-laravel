<?php

declare(strict_types=1);

use AcMarche\AldermenAgenda\Models\Recipient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Put both leftover columns back so the migration has something to drop. Production carries
 * `slugname` on `aldermen_recipients` and the fresh-install schema used to create `token`; both
 * are `not null` without a default, which is what breaks recipient creation.
 */
function restoreAldermenLegacyRecipientColumns(): void
{
    Schema::connection('maria-aldermen-agenda')->table('aldermen_recipients', function (Blueprint $table): void {
        $table->string('slugname')->default('');
        $table->string('token')->default('');
    });
}

function runDropLegacyRecipientColumnsMigration(): void
{
    $migration = require dirname(__DIR__, 2)
        .'/modules/AldermenAgenda/database/migrations/2026_08_26_074000_drop_legacy_recipient_columns.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

it('drops the leftover columns left behind on the recipients table', function (): void {
    restoreAldermenLegacyRecipientColumns();
    runDropLegacyRecipientColumnsMigration();

    expect(Schema::connection('maria-aldermen-agenda')->hasColumn('aldermen_recipients', 'slugname'))->toBeFalse()
        ->and(Schema::connection('maria-aldermen-agenda')->hasColumn('aldermen_recipients', 'token'))->toBeFalse();
});

it('lets a recipient be created once the leftover columns are gone', function (): void {
    restoreAldermenLegacyRecipientColumns();
    runDropLegacyRecipientColumnsMigration();

    $recipient = Recipient::create([
        'last_name' => 'Coppe',
        'first_name' => 'Magali',
        'email' => 'magali.coppe@ac.marche.be',
        'ics' => true,
    ]);

    expect($recipient->exists)->toBeTrue();
});

it('is a no-op when the leftover columns are already gone', function (): void {
    restoreAldermenLegacyRecipientColumns();
    runDropLegacyRecipientColumnsMigration();
    runDropLegacyRecipientColumnsMigration();

    expect(Schema::connection('maria-aldermen-agenda')->hasColumn('aldermen_recipients', 'slugname'))->toBeFalse();
});

it('does not create the unused token column on a fresh install', function (): void {
    expect(Schema::connection('maria-aldermen-agenda')->hasColumn('aldermen_recipients', 'token'))->toBeFalse();
});
