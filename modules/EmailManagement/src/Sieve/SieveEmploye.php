<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Sieve;

use Exception;
use PhpSieveManager\Exceptions\SieveException;
use PhpSieveManager\ManageSieve\Client;
use Throwable;

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

        try {
            if (! $client->connect($this->proxyUsername($asUser), $this->password, false, '', $this->authMechanism())) {
                throw new Exception($this->describeFailure($client, $asUser));
            }
        } catch (SieveException $e) {
            // connect() throws this rather than returning false when authentication is
            // refused, which would otherwise lose the server's own response.
            throw new Exception($this->describeFailure($client, $asUser, $e), $e->getCode(), $e);
        }

        return $this->client = $client;
    }

    /**
     * The account to authenticate as, in Dovecot's master user form.
     *
     * The library takes an authorisation id but its PLAIN and LOGIN mechanisms drop it on the
     * floor -- only DIGEST-MD5 sends one, and this server offers neither. So the account being
     * acted upon has to travel in the username instead, which is the same "user*admin" Dovecot
     * accepts over IMAP and which ImapEmploye already relies on.
     */
    public function proxyUsername(string $asUser): string
    {
        return $asUser.'*'.$this->user;
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

    /**
     * The mechanism to authenticate with, or null to let the client choose.
     *
     * Worth pinning: the client attempts only the first mechanism the server advertises and
     * gives up rather than trying the rest, so a server offering DIGEST-MD5 never reaches
     * PLAIN -- which is what admin proxy auth needs.
     */
    private function authMechanism(): ?string
    {
        $mechanism = config('email-management.sieve.auth_mechanism');

        return is_string($mechanism) && $mechanism !== '' ? $mechanism : null;
    }

    /**
     * Adds what the server said, and what it offered, to an otherwise bare failure.
     */
    private function describeFailure(Client $client, string $asUser, ?SieveException $e = null): string
    {
        $parts = array_filter([
            $e?->getMessage() ?: "Connexion Sieve refusée pour {$asUser}.",
            $client->getErrorMessage() ? 'Réponse du serveur : '.$client->getErrorMessage() : null,
            $this->describeMechanisms($client),
            'Compte : '.$this->user.', pour : '.$asUser.', serveur : '.$this->host.':'.$this->port,
        ]);

        return implode(' | ', $parts);
    }

    private function describeMechanisms(Client $client): ?string
    {
        try {
            $mechanisms = $client->getSASLMechanisms();
        } catch (Throwable) {
            return null;
        }

        return is_array($mechanisms) && $mechanisms !== []
            ? 'Mécanismes proposés : '.implode(', ', $mechanisms).($this->authMechanism() !== null ? ' (demandé : '.$this->authMechanism().')' : '')
            : null;
    }
}
