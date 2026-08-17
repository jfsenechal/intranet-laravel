<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Contract;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Write a raw value into the column, bypassing the model cast, the way the legacy
 * rows still sitting in the database hold it.
 */
function contractWithRawIsReplacement(string $value): Contract
{
    $contract = Contract::factory()->create();

    DB::connection('maria-hrm')
        ->table('contracts')
        ->where('id', $contract->getKey())
        ->update(['is_replacement' => $value]);

    return $contract;
}

function runIsReplacementMigration(): void
{
    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_08_17_000001_convert_is_replacement_to_boolean_on_contracts.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

it('converts the legacy oui value to true', function (): void {
    $contract = contractWithRawIsReplacement('oui');

    runIsReplacementMigration();

    expect($contract->fresh()->is_replacement)->toBeTrue();
});

it('converts every other legacy value to false', function (string $value): void {
    $contract = contractWithRawIsReplacement($value);

    runIsReplacementMigration();

    expect($contract->fresh()->is_replacement)->toBeFalse();
})->with(['non', '0', '']);

it('leaves values that are already true untouched', function (): void {
    $contract = contractWithRawIsReplacement('1');

    runIsReplacementMigration();

    expect($contract->fresh()->is_replacement)->toBeTrue();
});

it('can run twice without flipping any value', function (): void {
    $replacement = contractWithRawIsReplacement('oui');
    $regular = contractWithRawIsReplacement('non');

    runIsReplacementMigration();
    runIsReplacementMigration();

    expect($replacement->fresh()->is_replacement)->toBeTrue()
        ->and($regular->fresh()->is_replacement)->toBeFalse();
});
