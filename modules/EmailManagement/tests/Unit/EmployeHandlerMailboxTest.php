<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

beforeEach(function (): void {
    DirectoryEmulator::setup('default');

    // No IMAP credentials: the mailbox step is expected to be skipped rather than attempted.
    app()->instance(ImapEmploye::class, new ImapEmploye);
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

/**
 * @param  array<string, mixed>  $attributes
 */
function ldapEmploye(array $attributes): EmployeLdap
{
    $entry = new EmployeLdap;

    foreach ($attributes as $name => $value) {
        $entry->{$name} = $value;
    }

    $entry->inside(config('email-management.ldap.bases.employes'))->save();

    return $entry;
}

function employeLdapRepository(): EmployeLdapRepository
{
    return app(EmployeLdapRepository::class);
}

describe('setQuota', function (): void {
    it('writes the quota to otherPager', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);

        app(EmployeHandler::class)->setQuota('amartin', 2048);

        expect(employeLdapRepository()->getQuota(employeLdapRepository()->getEntry('amartin')))->toBe(2048.0);
    });

    it('refuses a quota of zero or less', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);

        expect(fn () => app(EmployeHandler::class)->setQuota('amartin', 0))
            ->toThrow(Exception::class, 'plus grand que 0');
    });

    it('reports an employe missing from the directory', function (): void {
        expect(fn () => app(EmployeHandler::class)->setQuota('fantome', 1024))
            ->toThrow(Exception::class, 'introuvable');
    });
});

describe('createEmail', function (): void {
    it('writes the address and the default quota to the directory', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);

        app(EmployeHandler::class)->createEmail('amartin', 'alice.martin@ac.marche.be');

        $entry = employeLdapRepository()->getEntry('amartin');

        expect($entry->getFirstAttribute('mail'))->toBe('alice.martin@ac.marche.be')
            ->and(employeLdapRepository()->getQuota($entry))->toBe((float) config('email-management.default_quota_mb'));
    });

    it('reports that the mailbox was not created when imap is unconfigured', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);

        expect(app(EmployeHandler::class)->createEmail('amartin', 'alice.martin@ac.marche.be'))->toBeFalse();
    });

    it('rejects a malformed address', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);

        expect(fn () => app(EmployeHandler::class)->createEmail('amartin', 'pas-une-adresse'))
            ->toThrow(Exception::class, 'format valide');
    });

    it('refuses an address already held by another account', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);
        ldapEmploye(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'Gathy', 'mail' => 'occupe@ac.marche.be']);

        expect(fn () => app(EmployeHandler::class)->createEmail('amartin', 'occupe@ac.marche.be'))
            ->toThrow(Exception::class, 'déjà utilisée');
    });

    it('takes an address held by another account when forced', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin']);
        ldapEmploye(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'Gathy', 'mail' => 'occupe@ac.marche.be']);

        app(EmployeHandler::class)->createEmail('amartin', 'occupe@ac.marche.be', force: true);

        expect(employeLdapRepository()->getEntry('amartin')->getFirstAttribute('mail'))->toBe('occupe@ac.marche.be');
    });

    it('lets an account keep its own address', function (): void {
        ldapEmploye(['cn' => 'Alice Martin', 'samaccountname' => 'amartin', 'sn' => 'Martin', 'mail' => 'alice.martin@ac.marche.be']);

        app(EmployeHandler::class)->createEmail('amartin', 'alice.martin@ac.marche.be');

        expect(employeLdapRepository()->getEntry('amartin')->getFirstAttribute('mail'))->toBe('alice.martin@ac.marche.be');
    });
});
