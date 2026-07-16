<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\EmailExtensionEnum;

it('splits an address into its local part and extension', function (string $mail, ?string $local, ?EmailExtensionEnum $extension): void {
    expect(EmailExtensionEnum::localPart($mail))->toBe($local)
        ->and(EmailExtensionEnum::fromAddress($mail))->toBe($extension);
})->with([
    'ac' => ['ana.aguirre@ac.marche.be', 'ana.aguirre', EmailExtensionEnum::EXTENSION_AC],
    'cpas' => ['b.gathy@cpas.marche.be', 'b.gathy', EmailExtensionEnum::EXTENSION_CPAS],
    'marche' => ['info@marche.be', 'info', EmailExtensionEnum::EXTENSION_MARCHE],
]);

it('rejoins into the address it was split from', function (): void {
    $mail = 'ana.aguirre@ac.marche.be';

    expect(EmailExtensionEnum::localPart($mail).EmailExtensionEnum::fromAddress($mail)->value)->toBe($mail);
});

it('matches the extension regardless of case', function (): void {
    expect(EmailExtensionEnum::fromAddress('Ana.Aguirre@AC.Marche.BE'))->toBe(EmailExtensionEnum::EXTENSION_AC);
});

it('reports an unknown domain rather than guessing', function (): void {
    expect(EmailExtensionEnum::fromAddress('ana@famenneardenne.be'))->toBeNull()
        ->and(EmailExtensionEnum::localPart('ana@famenneardenne.be'))->toBe('ana');
});

it('treats a value with no domain as a bare local part', function (): void {
    expect(EmailExtensionEnum::localPart('ana.aguirre'))->toBe('ana.aguirre')
        ->and(EmailExtensionEnum::fromAddress('ana.aguirre'))->toBeNull();
});

it('splits on the last @, not the first', function (): void {
    expect(EmailExtensionEnum::localPart('odd"@"name@ac.marche.be'))->toBe('odd"@"name')
        ->and(EmailExtensionEnum::fromAddress('odd"@"name@ac.marche.be'))->toBe(EmailExtensionEnum::EXTENSION_AC);
});

it('handles a null address', function (): void {
    expect(EmailExtensionEnum::localPart(null))->toBeNull()
        ->and(EmailExtensionEnum::fromAddress(null))->toBeNull();
});

it('labels each case with its domain', function (): void {
    expect(EmailExtensionEnum::EXTENSION_AC->getLabel())->toBe('@ac.marche.be')
        ->and(EmailExtensionEnum::EXTENSION_CPAS->getLabel())->toBe('@cpas.marche.be')
        ->and(EmailExtensionEnum::EXTENSION_MARCHE->getLabel())->toBe('@marche.be');
});
