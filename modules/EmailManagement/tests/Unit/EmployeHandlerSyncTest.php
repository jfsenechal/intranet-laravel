<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

beforeEach(function (): void {
    DirectoryEmulator::setup('default');
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function createLdapEmploye(array $attributes): EmployeLdap
{
    $entry = new EmployeLdap;

    foreach ($attributes as $name => $value) {
        $entry->{$name} = $value;
    }

    $entry->inside(config('email-management.ldap.bases.employes'))->save();

    return $entry;
}

it('mirrors directory entries keyed on samaccountname', function (): void {
    createLdapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'givenName' => 'Alice', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);
    createLdapEmploye(['cn' => 'Beatrice Gathy', 'samaccountname' => 'bgathy', 'givenName' => 'Beatrice', 'sn' => 'Gathy', 'mail' => 'b.gathy@ac.marche.be']);

    $count = app(EmployeHandler::class)->syncFromLdap();

    expect($count)->toBe(2)
        ->and(Employe::count())->toBe(2)
        ->and(Employe::where('samaccountname', 'amartin')->first()->mail)->toBe('alice.martin@ac.marche.be');
});

it('imports directory entries that have no mailbox', function (): void {
    createLdapEmploye(['cn' => 'Anita Harschene', 'samaccountname' => 'aharschene', 'givenName' => 'Anita', 'sn' => 'Harschene']);

    app(EmployeHandler::class)->syncFromLdap();

    $employe = Employe::where('samaccountname', 'aharschene')->first();

    expect($employe)->not->toBeNull()
        ->and($employe->mail)->toBeNull();
});

it('updates an existing mirror row rather than duplicating it', function (): void {
    Employe::factory()->create(['samaccountname' => 'amartin', 'sn' => 'Ancien', 'mail' => 'old@ac.marche.be']);

    createLdapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'givenName' => 'Alice', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);

    app(EmployeHandler::class)->syncFromLdap();

    expect(Employe::count())->toBe(1)
        ->and(Employe::first()->sn)->toBe('Martin');
});

it('prunes mirror rows that are absent from the directory', function (): void {
    Employe::factory()->create(['samaccountname' => 'parti']);

    createLdapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'givenName' => 'Alice', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);

    app(EmployeHandler::class)->syncFromLdap();

    expect(Employe::where('samaccountname', 'parti')->exists())->toBeFalse()
        ->and(Employe::count())->toBe(1);
});

it('keeps the mirror intact when the directory returns nothing', function (): void {
    Employe::factory(3)->create();

    expect(fn (): int => app(EmployeHandler::class)->syncFromLdap())
        ->toThrow(Exception::class)
        ->and(Employe::count())->toBe(3);
});

it('keeps the mirror intact when no entry exposes a samaccountname', function (): void {
    Employe::factory(3)->create();

    createLdapEmploye(['cn' => 'Alice Martin', 'givenName' => 'Alice', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);

    expect(fn (): int => app(EmployeHandler::class)->syncFromLdap())
        ->toThrow(Exception::class)
        ->and(Employe::count())->toBe(3);
});
