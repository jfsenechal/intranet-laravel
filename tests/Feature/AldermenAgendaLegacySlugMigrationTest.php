<?php

declare(strict_types=1);

use AcMarche\AldermenAgenda\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Put the legacy slug columns back so the migration has something to drop. The
 * fresh-install schema never creates them, and the rename migrations that were
 * supposed to remove them used Blueprint::removeColumn(), which only edits the
 * pending blueprint and never emits an `alter table ... drop column`.
 */
function restoreAldermenLegacySlugColumns(): void
{
    Schema::connection('maria-aldermen-agenda')->table('events', function (Blueprint $table): void {
        $table->string('slugname')->default('');
    });

    Schema::connection('maria-aldermen-agenda')->table('aldermen_recipients', function (Blueprint $table): void {
        $table->string('slug')->default('');
    });
}

function runDropLegacySlugColumnsMigration(): void
{
    $migration = require dirname(__DIR__, 2)
        .'/modules/AldermenAgenda/database/migrations/2026_08_11_144608_drop_legacy_slug_columns.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

beforeEach(function (): void {
    restoreAldermenLegacySlugColumns();
});

it('drops the legacy slug columns left behind by the rename migrations', function (): void {
    runDropLegacySlugColumnsMigration();

    expect(Schema::connection('maria-aldermen-agenda')->hasColumn('events', 'slugname'))->toBeFalse()
        ->and(Schema::connection('maria-aldermen-agenda')->hasColumn('aldermen_recipients', 'slug'))->toBeFalse();
});

it('lets an event be created once the legacy column is gone', function (): void {
    runDropLegacySlugColumnsMigration();

    $event = Event::create([
        'name' => 'Officialisation du jumelage',
        'event_type' => 'Invitation',
        'organizer' => 'Organisé par la Ville',
        'description' => 'Programme de la journée',
        'start_at' => '2026-09-05 08:30:00',
        'end_at' => '2026-09-05 14:30:00',
        'location' => 'Hôtel de Ville de Marche',
    ]);

    expect($event->exists)->toBeTrue();
});

it('is a no-op when the legacy columns are already gone', function (): void {
    runDropLegacySlugColumnsMigration();
    runDropLegacySlugColumnsMigration();

    expect(Schema::connection('maria-aldermen-agenda')->hasColumn('events', 'slugname'))->toBeFalse();
});
