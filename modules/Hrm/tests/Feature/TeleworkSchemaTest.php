<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Tests\Feature;

use AcMarche\Hrm\Models\Telework;
use Illuminate\Support\Facades\Schema;

/**
 * Guards the legacy `teletravail` rename: `code_postal` was left untranslated, so
 * every insert failed with "Unknown column 'postal_code'" on databases migrated
 * from the legacy schema.
 */
it('renames code_postal to postal_code on teleworks', function (): void {
    expect(Schema::connection('maria-hrm')->hasColumn('teleworks', 'code_postal'))->toBeFalse()
        ->and(Schema::connection('maria-hrm')->hasColumn('teleworks', 'postal_code'))->toBeTrue();
});

it('has a column for every fillable attribute of the telework model', function (): void {
    $missing = array_values(array_filter(
        (new Telework)->getFillable(),
        fn (string $column): bool => ! Schema::connection('maria-hrm')->hasColumn('teleworks', $column),
    ));

    expect($missing)->toBe([]);
});
