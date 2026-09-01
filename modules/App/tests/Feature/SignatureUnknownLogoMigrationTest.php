<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use AcMarche\App\Models\Signature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Write the logo straight into the column, bypassing the enum cast, the way a row
 * saved before a SignatureEnum case was renamed still holds it.
 */
function signatureWithRawLogo(?string $logo): Signature
{
    $signature = Signature::create([
        'username' => 'jfsenechal',
        'first_name' => 'Jean-François',
        'last_name' => 'Senechal',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'jf@marche.be',
        'logo' => SignatureEnum::CPAS,
    ]);

    DB::table('signatures')
        ->where('id', $signature->getKey())
        ->update(['logo' => $logo]);

    return $signature;
}

function runUnknownSignatureLogoMigration(): void
{
    $migration = require dirname(__DIR__, 2).'/database/migrations/2026_09_01_120000_reset_unknown_signature_logos_to_default.php';

    expect($migration)->toBeInstanceOf(Migration::class);

    $migration->up();
}

it('falls a logo the enum no longer knows back to the commune logo', function (string $unknownLogo): void {
    $signature = signatureWithRawLogo($unknownLogo);

    runUnknownSignatureLogoMigration();

    expect($signature->fresh()->logo)->toBe(SignatureEnum::MARCHE);
})->with(['mtfa.png', 'mdt.jpg', 'deleted-partner.jpg']);

it('leaves a logo that is still a valid value untouched', function (): void {
    $signature = signatureWithRawLogo(SignatureEnum::CPAS->value);

    runUnknownSignatureLogoMigration();

    expect($signature->fresh()->logo)->toBe(SignatureEnum::CPAS);
});

it('leaves a signature without a logo alone', function (?string $noLogo): void {
    $signature = signatureWithRawLogo($noLogo);

    runUnknownSignatureLogoMigration();

    expect($signature->fresh()->logo)->toBeNull();
})->with([null, '']);
