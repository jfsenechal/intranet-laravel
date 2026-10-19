<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Console\Commands;

use AcMarche\EmailManagement\Sieve\SieveEmploye;
use Illuminate\Console\Command;
use Net_Sieve;
use Override;
use PEAR_Error;
use Symfony\Component\Console\Command\Command as SfCommand;
use Throwable;

/**
 * Diagnoses a ManageSieve connection one step at a time.
 *
 * The vacation action only reports that the login failed. This walks the same path -- socket,
 * capabilities, mechanisms, authentication -- and reports what the server actually offered and answered.
 */
final class SieveCheckCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'email-management:sieve-check
                            {user? : sAMAccountName to act on behalf of, e.g. jdupont (not the mail address)}
                            {--mechanism= : force a SASL mechanism, overriding config}';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Diagnose the ManageSieve connection used by the vacation action';

    public function handle(): int
    {
        $host = config('email-management.sieve.host');
        $port = (int) config('email-management.sieve.port', 4190);
        $admin = config('email-management.sieve.user');
        $password = config('email-management.sieve.password');
        $mechanism = $this->option('mechanism') ?: config('email-management.sieve.auth_mechanism');
        $asUser = $this->argument('user');

        $this->components->twoColumnDetail('Serveur', $host.':'.$port);
        $this->components->twoColumnDetail('Compte admin', $admin ?: '<non configuré>');
        $this->components->twoColumnDetail('Mot de passe', $password ? '<défini, '.mb_strlen((string) $password).' caractères>' : '<non configuré>');
        $this->components->twoColumnDetail('Mécanisme demandé', $mechanism ?: '<au choix du client>');
        $this->components->twoColumnDetail('Pour le compte', $asUser ?: '<aucun, test admin seul>');
        $this->newLine();

        if (! SieveEmploye::fromConfig()->isConfigured()) {
            $this->components->error('SIEVE_HOST / SIEVE_ADMIN / SIEVE_PWD ne sont pas tous définis.');

            return SfCommand::FAILURE;
        }

        if (! $this->checkSocket((string) $host, $port)) {
            return SfCommand::FAILURE;
        }

        return $this->checkClient((string) $admin, (string) $password, $mechanism, $asUser);
    }

    private function checkSocket(string $host, int $port): bool
    {
        $socket = @fsockopen($host, $port, $errorNumber, $errorString, 5);

        if ($socket === false) {
            $this->components->error("Le port {$port} est injoignable depuis cette machine : {$errorString} ({$errorNumber})");
            $this->line('  Le pare-feu ou le serveur refuse la connexion. Rien à voir avec les identifiants.');

            return false;
        }

        fclose($socket);
        $this->components->info("Le port {$port} est joignable.");

        return true;
    }

    private function checkClient(string $admin, string $password, ?string $mechanism, ?string $asUser): int
    {
        $sieveEmploye = SieveEmploye::fromConfig();

        try {
            $client = $sieveEmploye->connect();
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return SfCommand::FAILURE;
        }

        $this->reportCapabilities($client);
        $this->newLine();

        // The same username the vacation action authenticates with, so this exercises the real
        // path rather than one the application never takes.
        $username = $asUser !== null ? $sieveEmploye->proxyUsername($asUser) : $admin;

        $this->components->twoColumnDetail('Authentification', $username);

        $result = $client->login($username, $password, $mechanism ?: null);

        if ($result instanceof PEAR_Error) {
            $client->disconnect();
            $this->newLine();
            $this->components->error('Authentification refusée : '.mb_trim((string) $result->getMessage(), '" '));
            $this->newLine();
            $this->line('  Pistes :');

            if ($asUser !== null && str_contains($asUser, '@')) {
                $this->line("  - Attendu ici : le sAMAccountName (par exemple judpont), pas l'adresse mail.");
            }

            $this->line("  - Le compte {$admin} est-il déclaré master user côté Dovecot (auth_master_user_separator, passdb master) ?");
            $this->line('  - Essayez un autre mécanisme :');
            $this->line("      php artisan email-management:sieve-check {$asUser} --mechanism=LOGIN");

            return SfCommand::FAILURE;
        }

        $this->newLine();
        $this->components->info('Authentification réussie'.($asUser !== null ? " pour {$asUser}." : '.'));

        if ($asUser !== null) {
            $this->reportScripts($sieveEmploye, $client);
        }

        $client->disconnect();

        return SfCommand::SUCCESS;
    }

    private function reportCapabilities(Net_Sieve $client): void
    {
        foreach (['SASL' => $client->getAuthMechs(), 'Extensions' => $client->getExtensions()] as $name => $values) {
            $this->components->twoColumnDetail(
                $name,
                is_array($values) ? implode(', ', $values) : '<illisible>',
            );
        }
    }

    private function reportScripts(SieveEmploye $sieveEmploye, Net_Sieve $client): void
    {
        try {
            $scripts = $sieveEmploye->listScripts($client);
        } catch (Throwable $e) {
            $this->components->warn('Scripts illisibles : '.$e->getMessage());

            return;
        }

        $this->components->twoColumnDetail(
            'Scripts existants',
            $scripts !== [] ? implode(', ', array_keys($scripts)) : '<aucun>',
        );
        $this->components->twoColumnDetail(
            'Script actif',
            SieveEmploye::findActiveScript($scripts) ?? '<aucun>',
        );
    }
}
