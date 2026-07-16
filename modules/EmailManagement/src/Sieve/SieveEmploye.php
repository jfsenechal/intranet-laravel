<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Sieve;

use Exception;
use PhpSieveManager\ManageSieve\Client;

/**
 * ManageSieve scripts for staff accounts, reached with the admin account.
 *
 * The admin authenticates and then acts on behalf of the account through the
 * authorisation id, the same way ImapEmploye proxies over IMAP.
 */
final class SieveEmploye
{
    private ?Client $client = null;

    public function __construct(
        private readonly ?string $host = null,
        private readonly int $port = 4190,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            config('email-management.sieve.host'),
            (int) config('email-management.sieve.port', 4190),
            config('email-management.sieve.user'),
            config('email-management.sieve.password'),
        );
    }

    public function isConfigured(): bool
    {
        return $this->host !== null && $this->host !== ''
            && $this->user !== null && $this->user !== ''
            && $this->password !== null && $this->password !== '';
    }

    /**
     * @throws Exception
     */
    public function login(string $asUser): Client
    {
        if (! $this->isConfigured()) {
            throw new Exception('Les identifiants Sieve ne sont pas configurés (SIEVE_*).');
        }

        $client = new Client($this->host, $this->port);

        if (! $client->connect($this->user, $this->password, false, $asUser)) {
            throw new Exception($client->getErrorMessage() ?: "Connexion Sieve refusée pour {$asUser}.");
        }

        return $this->client = $client;
    }

    /**
     * Installs the out-of-office script and makes it the active one.
     *
     * @throws Exception
     */
    public function setVacation(string $user, string $script): void
    {
        try {
            $client = $this->login($user);

            if (! $client->putScript(VacationScript::SCRIPT_NAME, $script)) {
                throw new Exception($client->getErrorMessage() ?: "Le script n'a pas été accepté par le serveur.");
            }

            if (! $client->activateScript(VacationScript::SCRIPT_NAME)) {
                throw new Exception($client->getErrorMessage() ?: "Le script n'a pas pu être activé.");
            }
        } finally {
            $this->close();
        }
    }

    /**
     * The account's current out-of-office script, or null when it has none.
     *
     * @throws Exception
     */
    public function getVacation(string $user): ?string
    {
        try {
            $script = $this->login($user)->getScript(VacationScript::SCRIPT_NAME);

            return is_string($script) && $script !== '' ? $script : null;
        } finally {
            $this->close();
        }
    }

    /**
     * @throws Exception
     */
    public function removeVacation(string $user): void
    {
        try {
            $client = $this->login($user);

            if (! $client->removeScripts(VacationScript::SCRIPT_NAME)) {
                throw new Exception($client->getErrorMessage() ?: "Le script n'a pas pu être supprimé.");
            }
        } finally {
            $this->close();
        }
    }

    public function close(): void
    {
        $this->client?->close();
        $this->client = null;
    }
}
