<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use AcMarche\App\Models\Signature;

/**
 * Build a model straight from a stored row so the logo goes through the accessor
 * exactly as it does when Eloquent hydrates a record from the database.
 */
function signatureStoring(?string $logo): Signature
{
    return (new Signature)->setRawAttributes(['logo' => $logo]);
}

it('reads a file name the enum no longer knows as the commune logo', function (string $unknownLogo): void {
    expect(signatureStoring($unknownLogo)->logo)->toBe(SignatureEnum::MARCHE);
})->with(['mtfa.png', 'mdt.jpg', 'deleted-partner.jpg']);

it('reads a file name the enum still knows as its own case', function (): void {
    expect(signatureStoring(SignatureEnum::CPAS->value)->logo)->toBe(SignatureEnum::CPAS);
});

it('reads a missing logo as no logo', function (?string $noLogo): void {
    expect(signatureStoring($noLogo)->logo)->toBeNull();
})->with([null, '']);

it('stores the file name of the case it is given', function (): void {
    $signature = new Signature;
    $signature->logo = SignatureEnum::CPAS;

    expect($signature->getAttributes()['logo'])->toBe('cpas.jpg');
});

it('stores a file name the enum no longer knows as the commune logo', function (): void {
    $signature = new Signature;
    $signature->logo = 'mtfa.png';

    expect($signature->getAttributes()['logo'])->toBe('marche.jpg');
});

it('stores no logo as null', function (): void {
    $signature = new Signature;
    $signature->logo = null;

    expect($signature->getAttributes()['logo'])->toBeNull();
});
