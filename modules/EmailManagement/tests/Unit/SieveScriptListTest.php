<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Sieve\SieveEmploye;

it('flags the script marked active in a listscripts response', function (): void {
    $scripts = SieveEmploye::parseScriptList("\"roundcube\"\r\n\"vacation\" ACTIVE\r\n");

    expect($scripts)->toBe(['roundcube' => false, 'vacation' => true])
        ->and(SieveEmploye::findActiveScript($scripts))->toBe('vacation');
});

it('reports no active script when none is marked', function (): void {
    $scripts = SieveEmploye::parseScriptList("\"vacation\"\r\n");

    expect($scripts)->toBe(['vacation' => false])
        ->and(SieveEmploye::findActiveScript($scripts))->toBeNull();
});

it('unescapes quoted script names', function (): void {
    expect(SieveEmploye::parseScriptList("\"mon \\\"filtre\\\"\" ACTIVE\r\n"))->toBe(['mon "filtre"' => true]);
});
