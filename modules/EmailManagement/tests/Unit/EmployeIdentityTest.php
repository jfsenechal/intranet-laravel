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
 * Every identity attribute set to a distinguishable value.
 *
 * @return array<string, string>
 */
function identityAttributes(): array
{
    return [
        'givenName' => 'Alice',
        'sn' => 'Martin',
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
    saveLdapEntry([...identityAttributes(), 'cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'mail' => 'alice@ac.marche.be']);

    app(EmployeHandler::class)->syncFromLdap();

    $employe = Employe::where('samaccountname', 'amartin')->first();

    foreach (identityAttributes() as $attribute => $expected) {
        expect($employe->{$attribute})->toBe($expected, "attribute {$attribute}");
    }
});
