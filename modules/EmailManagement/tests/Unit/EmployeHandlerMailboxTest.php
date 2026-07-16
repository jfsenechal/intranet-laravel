<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Imap\ImapEmploye;
use AcMarche\EmailManagement\Ldap\EmployeHandler;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Models\Employe;
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

function repository(): EmployeLdapRepository
{
    return app(EmployeLdapRepository::class);
}

describe('setQuota', function (): void {
    it('writes the quota to otherPager', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        app(EmployeHandler::class)->setQuota($employe, 2048);

        expect(repository()->getQuota(repository()->getEntry('aaguirre')))->toBe(2048.0);
    });

    it('refuses a quota of zero or less', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        expect(fn () => app(EmployeHandler::class)->setQuota($employe, 0))
            ->toThrow(Exception::class, 'plus grand que 0');
    });

    it('reports an employe missing from the directory', function (): void {
        $employe = Employe::factory()->create(['samaccountname' => 'fantome']);

        expect(fn () => app(EmployeHandler::class)->setQuota($employe, 1024))
            ->toThrow(Exception::class, 'introuvable');
    });
});

describe('updateAliases', function (): void {
    it('replaces the alias list', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre', 'proxyAddresses' => ['vieux@ac.marche.be']]);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        app(EmployeHandler::class)->updateAliases($employe, ['a.aguirre@ac.marche.be', 'ana@ac.marche.be']);

        expect(repository()->getAliases(repository()->getEntry('aaguirre')))
            ->toBe(['a.aguirre@ac.marche.be', 'ana@ac.marche.be']);
    });

    it('clears every alias when given an empty list', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre', 'proxyAddresses' => ['vieux@ac.marche.be']]);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        app(EmployeHandler::class)->updateAliases($employe, []);

        expect(repository()->getAliases(repository()->getEntry('aaguirre')))->toBe([]);
    });

    it('rejects a malformed alias', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        expect(fn () => app(EmployeHandler::class)->updateAliases($employe, ['pas-une-adresse']))
            ->toThrow(Exception::class, 'format valide');
    });

    it('refuses an alias already held by another account', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        ldapEmploye(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'Gathy', 'mail' => 'occupe@ac.marche.be']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        expect(fn () => app(EmployeHandler::class)->updateAliases($employe, ['occupe@ac.marche.be']))
            ->toThrow(Exception::class, 'déjà utilisée');
    });
});

describe('createEmail', function (): void {
    it('writes the address and the default quota to the directory', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre', 'mail' => null]);

        app(EmployeHandler::class)->createEmail($employe, 'ana.aguirre@ac.marche.be');

        $entry = repository()->getEntry('aaguirre');

        expect($entry->getFirstAttribute('mail'))->toBe('ana.aguirre@ac.marche.be')
            ->and(repository()->getQuota($entry))->toBe((float) config('email-management.default_quota_mb'))
            ->and($employe->refresh()->mail)->toBe('ana.aguirre@ac.marche.be');
    });

    it('reports that the mailbox was not created when imap is unconfigured', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre', 'mail' => null]);

        expect(app(EmployeHandler::class)->createEmail($employe, 'ana.aguirre@ac.marche.be'))->toBeFalse();
    });

    it('rejects a malformed address', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre']);

        expect(fn () => app(EmployeHandler::class)->createEmail($employe, 'pas-une-adresse'))
            ->toThrow(Exception::class, 'format valide');
    });

    it('refuses an address already held by another account', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        ldapEmploye(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'Gathy', 'mail' => 'occupe@ac.marche.be']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre', 'mail' => null]);

        expect(fn () => app(EmployeHandler::class)->createEmail($employe, 'occupe@ac.marche.be'))
            ->toThrow(Exception::class, 'déjà utilisée');
    });

    it('takes an address held by another account when forced', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre']);
        ldapEmploye(['cn' => 'Bea Gathy', 'samaccountname' => 'bgathy', 'sn' => 'Gathy', 'mail' => 'occupe@ac.marche.be']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre', 'mail' => null]);

        app(EmployeHandler::class)->createEmail($employe, 'occupe@ac.marche.be', force: true);

        expect(repository()->getEntry('aaguirre')->getFirstAttribute('mail'))->toBe('occupe@ac.marche.be');
    });

    it('lets an account keep its own address', function (): void {
        ldapEmploye(['cn' => 'Ana Aguirre', 'samaccountname' => 'aaguirre', 'sn' => 'Aguirre', 'mail' => 'ana.aguirre@ac.marche.be']);
        $employe = Employe::factory()->create(['samaccountname' => 'aaguirre', 'mail' => 'ana.aguirre@ac.marche.be']);

        app(EmployeHandler::class)->createEmail($employe, 'ana.aguirre@ac.marche.be');

        expect(repository()->getEntry('aaguirre')->getFirstAttribute('mail'))->toBe('ana.aguirre@ac.marche.be');
    });
});
