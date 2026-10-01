<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Absence;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

/**
 * The absences table predates this app, so its `start_date` arrived nullable and
 * accumulated rows without one. This migration clears those rows and closes the
 * column, backing the `required()` rule on the absence form.
 */
function absenceStartDateMigration(): Migration
{
    $migration = require dirname(__DIR__, 2)
        .'/modules/Hrm/database/migrations/2026_10_01_000001_make_start_date_required_on_absences.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    return $migration;
}

beforeEach(function (): void {
    // The migration already ran against the test schema; reopen the column so the
    // legacy state can be reproduced.
    absenceStartDateMigration()->down();
});

it('deletes the absences without a start date and keeps the others', function (): void {
    $withoutStartDate = Absence::factory()->create(['start_date' => null]);
    $withStartDate = Absence::factory()->create(['start_date' => '2026-03-02']);

    absenceStartDateMigration()->up();

    expect(Absence::query()->whereKey($withoutStartDate->id)->exists())->toBeFalse()
        ->and(Absence::query()->whereKey($withStartDate->id)->exists())->toBeTrue();
});

it('closes the column so a null start date can no longer be stored', function (): void {
    absenceStartDateMigration()->up();

    expect(fn (): Absence => Absence::factory()->create(['start_date' => null]))
        ->toThrow(QueryException::class);
});

it('reopens the column on rollback', function (): void {
    absenceStartDateMigration()->up();
    absenceStartDateMigration()->down();

    $absence = Absence::factory()->create(['start_date' => null]);

    expect($absence->refresh()->start_date)->toBeNull()
        ->and(Schema::connection('maria-hrm')->hasColumn('absences', 'start_date'))->toBeTrue();
});
