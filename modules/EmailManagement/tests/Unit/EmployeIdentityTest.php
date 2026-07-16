<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

beforeEach(function (): void {
    DirectoryEmulator::setup('default');
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

/**
 * Every identity attribute set to a distinguishable value.
 *
 * @return array<string, string>
 */
function identityAttributes(): array
{
    return [
        'givenName' => 'Ana',
        'sn' => 'Aguirre',
        'initials' => 'AA',
        'title' => 'Attachée',
        'company' => 'AC Marche',
        'department' => 'CEE - Enseignement',
        'co' => 'Belgique',
        'description' => 'INBOX',
        'info' => 'compte créé en 2017',
        'wWWHomePage' => 'www.marche.be',
        'streetAddress' => 'Rue Victor Libert, 36E',
        'postalCode' => '6900',
        'l' => 'Marche-en-Famenne',
        'telephoneNumber' => '+3284326991',
        'ipPhone' => '9991',
        'mobile' => '+32477320320',
    ];
}

function saveLdapEntry(array $attributes): EmployeLdap
{
    $entry = new EmployeLdap;

    foreach ($attributes as $name => $value) {
        $entry->{$name} = $value;
    }

    $entry->inside(config('email-management.ldap.bases.employes'))->save();

    return $entry;
}

it('pulls every identity attribute out of the directory', function (): void {
    saveLdapEntry([...identityAttributes(), 'cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'mail' => 'ana@ac.marche.be']);

    app(EmployeHandler::class)->syncFromLdap();

    $employe = Employe::where('samaccountname', 'aaguirre')->first();

    foreach (identityAttributes() as $attribute => $expected) {
        expect($employe->{$attribute})->toBe($expected, "attribute {$attribute}");
    }
});

it('pushes every identity attribute back to the directory', function (): void {
    saveLdapEntry(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Ancien']);

    $employe = Employe::factory()->create([...identityAttributes(), 'samaccountname' => 'aaguirre', 'mail' => 'ana@ac.marche.be']);
    $ldapEntry = app(EmployeLdapRepository::class)->getEntry('aaguirre');

    app(EmployeHandler::class)->updateEmploye($employe, $ldapEntry);

    $saved = app(EmployeLdapRepository::class)->getEntry('aaguirre');

    foreach (identityAttributes() as $attribute => $expected) {
        expect($saved->getFirstAttribute($attribute))->toBe($expected, "attribute {$attribute}");
    }
});

it('removes an attribute from the directory when the field is cleared', function (): void {
    saveLdapEntry([...identityAttributes(), 'cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre']);

    $employe = Employe::factory()->create([
        ...identityAttributes(),
        'samaccountname' => 'aaguirre',
        'mobile' => null,
        'title' => null,
    ]);

    app(EmployeHandler::class)->updateEmploye($employe, app(EmployeLdapRepository::class)->getEntry('aaguirre'));

    $saved = app(EmployeLdapRepository::class)->getEntry('aaguirre');

    expect($saved->getFirstAttribute('mobile'))->toBeNull()
        ->and($saved->getFirstAttribute('title'))->toBeNull()
        ->and($saved->getFirstAttribute('company'))->toBe('AC Marche');
});

it('derives displayName from the name fields rather than mirroring it', function (): void {
    saveLdapEntry(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Ancien', 'displayName' => 'Ancien']);

    $employe = Employe::factory()->create([
        'samaccountname' => 'aaguirre',
        'givenName' => 'Ana',
        'sn' => 'Aguirre',
    ]);

    app(EmployeHandler::class)->updateEmploye($employe, app(EmployeLdapRepository::class)->getEntry('aaguirre'));

    expect(app(EmployeLdapRepository::class)->getEntry('aaguirre')->getFirstAttribute('displayName'))
        ->toBe('Ana Aguirre');
});
