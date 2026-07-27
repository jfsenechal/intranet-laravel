<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use Illuminate\Support\Facades\Schema;

/**
 * Guards the pst migration chain: the follow-up migrations used to bail out on a
 * `Schema::hasTable('actions')` check that was always true, so a freshly migrated
 * database kept the shape of the initial create migration.
 */
it('does not keep a user_id column on the action pivot tables', function (string $table): void {
    expect(Schema::connection('maria-pst')->hasColumn($table, 'user_id'))->toBeFalse()
        ->and(Schema::connection('maria-pst')->hasColumn($table, 'username'))->toBeTrue();
})->with(['action_user', 'action_mandatory']);

it('renames is_internal to scope on actions and strategic objectives', function (string $table): void {
    expect(Schema::connection('maria-pst')->hasColumn($table, 'is_internal'))->toBeFalse()
        ->and(Schema::connection('maria-pst')->hasColumn($table, 'scope'))->toBeTrue();
})->with(['actions', 'strategic_objectives']);

it('adds scope to operational objectives', function (): void {
    expect(Schema::connection('maria-pst')->hasColumn('operational_objectives', 'scope'))->toBeTrue();
});

it('renames to_validate to validated on actions', function (): void {
    expect(Schema::connection('maria-pst')->hasColumn('actions', 'to_validate'))->toBeFalse()
        ->and(Schema::connection('maria-pst')->hasColumn('actions', 'validated'))->toBeTrue();
});

it('keeps synergy on actions only', function (): void {
    expect(Schema::connection('maria-pst')->hasColumn('actions', 'synergy'))->toBeTrue()
        ->and(Schema::connection('maria-pst')->hasColumn('strategic_objectives', 'synergy'))->toBeFalse()
        ->and(Schema::connection('maria-pst')->hasColumn('operational_objectives', 'synergy'))->toBeFalse();
});

it('adds soft deletes to actions', function (): void {
    expect(Schema::connection('maria-pst')->hasColumn('actions', 'deleted_at'))->toBeTrue();
});

/**
 * The two make_department_*_nullable migrations shared a timestamp, and the alphabetical
 * tie-break ran them in the wrong order, so a fresh database ended up nullable.
 */
it('requires a department on objectives', function (string $table): void {
    $column = collect(Schema::connection('maria-pst')->getColumns($table))
        ->firstWhere('name', 'department');

    expect($column['nullable'])->toBeFalse();
})->with(['strategic_objectives', 'operational_objectives']);
