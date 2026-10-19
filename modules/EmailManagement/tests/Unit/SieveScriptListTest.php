<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Sieve\SieveEmploye;

it('flags the active script among the listed ones', function (): void {
    $scripts = SieveEmploye::indexScripts(['roundcube', 'vacation'], 'vacation');

    expect($scripts)->toBe(['roundcube' => false, 'vacation' => true])
        ->and(SieveEmploye::findActiveScript($scripts))->toBe('vacation');
});

it('reports no active script when none is active', function (): void {
    $scripts = SieveEmploye::indexScripts(['vacation'], null);

    expect($scripts)->toBe(['vacation' => false])
        ->and(SieveEmploye::findActiveScript($scripts))->toBeNull();
});

it('turns a client error into an exception carrying the server response', function (): void {
    SieveEmploye::guard(new PEAR_Error('"Authentication failed."', 3), 'Connexion refusée.');
})->throws(Exception::class, 'Connexion refusée. Réponse du serveur : Authentication failed.');

it('passes a successful client result through', function (): void {
    expect(SieveEmploye::guard(['vacation'], 'Impossible de lister les scripts.'))->toBe(['vacation']);
});
