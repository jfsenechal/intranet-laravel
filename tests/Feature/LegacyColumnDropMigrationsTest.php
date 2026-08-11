<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy rename migrations dropped columns with Blueprint::removeColumn(), which only
 * strips a column from the pending blueprint and never emits an `alter table ... drop
 * column`. Every one of those columns therefore survived on already-migrated databases.
 *
 * Each case restores the legacy columns the fresh-install schema never creates, runs the
 * catch-up migration, and asserts the columns are really gone.
 *
 * @return array<string, array{0: string, 1: string, 2: array<int, string>, 3: string}>
 */
function legacyColumnDropCases(): array
{
    return [
        'conseil agendas' => [
            'maria-conseil',
            'agendas',
            ['date_fin_diffusion'],
            'modules/Conseil/database/migrations/2026_08_11_145301_drop_legacy_agenda_diffusion_column.php',
        ],
        'meal delivery clients' => [
            'maria-meal-delivery',
            'clients',
            ['slugname', 'tournee_save'],
            'modules/MealDelivery/database/migrations/2026_08_11_145302_drop_legacy_client_columns.php',
        ],
        'courrier recipients' => [
            'maria-courrier',
            'recipients',
            ['actif'],
            'modules/Courrier/database/migrations/2026_08_11_145303_drop_legacy_actif_columns.php',
        ],
        'courrier services' => [
            'maria-courrier',
            'courrier_services',
            ['actif'],
            'modules/Courrier/database/migrations/2026_08_11_145303_drop_legacy_actif_columns.php',
        ],
        'hrm employees' => [
            'maria-hrm',
            'employees',
            ['employeur_save_id', 'phone_office', 'mobile_office'],
            'modules/Hrm/database/migrations/2026_08_11_145304_drop_legacy_employee_office_columns.php',
        ],
        'sports members' => [
            'maria-rescam',
            'sports_members',
            ['user'],
            'modules/SportsActivities/database/migrations/2026_08_11_145306_drop_legacy_member_user_column.php',
        ],
    ];
}

/**
 * @param  array<int, string>  $columns
 */
function restoreLegacyColumns(string $connection, string $table, array $columns): void
{
    Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($columns): void {
        foreach ($columns as $column) {
            $blueprint->string($column)->nullable();
        }
    });
}

function runLegacyDropMigration(string $path): void
{
    $migration = require dirname(__DIR__, 2).'/'.$path;

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

it('drops the legacy columns the rename migrations only pretended to remove', function (
    string $connection,
    string $table,
    array $columns,
    string $path,
): void {
    restoreLegacyColumns($connection, $table, $columns);

    foreach ($columns as $column) {
        expect(Schema::connection($connection)->hasColumn($table, $column))->toBeTrue();
    }

    runLegacyDropMigration($path);

    foreach ($columns as $column) {
        expect(Schema::connection($connection)->hasColumn($table, $column))->toBeFalse();
    }
})->with(legacyColumnDropCases());

it('is a no-op when the legacy columns are already gone', function (
    string $connection,
    string $table,
    array $columns,
    string $path,
): void {
    runLegacyDropMigration($path);
    runLegacyDropMigration($path);

    foreach ($columns as $column) {
        expect(Schema::connection($connection)->hasColumn($table, $column))->toBeFalse();
    }
})->with(legacyColumnDropCases());
