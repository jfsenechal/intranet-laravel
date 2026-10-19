<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Sieve;

use Exception;
use Net_Sieve;
use PEAR_Error;

/**
 * ManageSieve scripts for staff accounts, reached with the admin account.
 *
 * The admin authenticates and then acts on behalf of the account, the same way
 * ImapEmploye proxies over IMAP.
 */
final class SieveEmploye
{
    private ?Net_Sieve $client = null;

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

    /**
     * Every script name, keyed to whether it is the active one.
     *
     * @param  list<string>  $names
     * @return array<string, bool>
     */
    public static function indexScripts(array $names, ?string $active): array
    {
        $scripts = [];

        foreach ($names as $name) {
            $scripts[$name] = $name === $active;
        }

        return $scripts;
    }

    /**
     * @param  array<string, bool>  $scripts
     */
    public static function findActiveScript(array $scripts): ?string
    {
        $active = array_search(true, $scripts, true);

        return is_string($active) ? $active : null;
    }

    /**
     * Net_Sieve reports failures by returning a PEAR_Error rather than throwing;
     * this turns one into an exception and passes any other result through.
     *
     * @template T
     *
     * @param  T|PEAR_Error  $result
     * @return T
     *
     * @throws Exception
     */
    public static function guard(mixed $result, string $context): mixed
    {
        if ($result instanceof PEAR_Error) {
            throw new Exception($context.' Réponse du serveur : '.mb_trim((string) $result->getMessage(), '" '), (int) $result->getCode());
        }

        return $result;
    }

    public function isConfigured(): bool
    {
        return $this->host !== null && $this->host !== ''
            && $this->user !== null && $this->user !== ''
            && $this->password !== null && $this->password !== '';
    }

    /**
     * Opens the connection without authenticating, so a diagnosis can read what the
     * server offers before the login is attempted.
     *
     * TLS is left off, as with the previous client: the server is reached on the internal network.
     *
     * @throws Exception
     */
    public function connect(): Net_Sieve
    {
        if (! $this->isConfigured()) {
            throw new Exception('Les identifiants Sieve ne sont pas configurés (SIEVE_*).');
        }

        $client = new Net_Sieve();

        self::guard(
            $client->connect($this->host, (string) $this->port, null, false),
            "Serveur Sieve injoignable ({$this->host}:{$this->port}).",
        );

        return $client;
    }

    /**
     * @throws Exception
     */
    public function login(string $asUser): Net_Sieve
    {
        $client = $this->connect();

        $result = $client->login($this->proxyUsername($asUser), $this->password, $this->authMechanism());

        if ($result instanceof PEAR_Error) {
            $message = $this->describeFailure($client, $asUser, $result);
            $client->disconnect();

            throw new Exception($message, (int) $result->getCode());
        }

        return $this->client = $client;
    }

    /**
     * The account to authenticate as, in Dovecot's master user form.
     *
     * The same "user*admin" Dovecot accepts over IMAP and which ImapEmploye already relies on.
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
            self::guard(
                $this->login($user)->installScript(VacationScript::SCRIPT_NAME, $script, true),
                "Le script n'a pas été accepté par le serveur.",
            );
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
            $client = $this->login($user);

            if (! array_key_exists(VacationScript::SCRIPT_NAME, $this->listScripts($client))) {
                return null;
            }

            $script = self::guard($client->getScript(VacationScript::SCRIPT_NAME), 'Impossible de lire le script.');

            return $script !== '' ? $script : null;
        } finally {
            $this->close();
        }
    }

    /**
     * The name of the account's active script, or null when none is active.
     *
     * @throws Exception
     */
    public function getActiveScript(string $user): ?string
    {
        try {
            return self::findActiveScript($this->listScripts($this->login($user)));
        } finally {
            $this->close();
        }
    }

    /**
     * Whether the out-of-office script is the one the server currently runs.
     *
     * @throws Exception
     */
    public function isVacationActive(string $user): bool
    {
        return $this->getActiveScript($user) === VacationScript::SCRIPT_NAME;
    }

    /**
     * Every script on the account, keyed by name, with whether it is the active one.
     *
     * @return array<string, bool>
     *
     * @throws Exception
     */
    public function listScripts(Net_Sieve $client): array
    {
        $active = null;
        $names = self::guard($client->listScripts($active), 'Impossible de lister les scripts.');

        return self::indexScripts(array_values((array) $names), is_string($active) ? $active : null);
    }

    /**
     * @throws Exception
     */
    public function removeVacation(string $user): void
    {
        try {
            $client = $this->login($user);

            // The server refuses to delete the active script (RFC 5804), so it is switched off first.
            if (self::findActiveScript($this->listScripts($client)) === VacationScript::SCRIPT_NAME) {
                self::guard($client->setActive(''), "Le script n'a pas pu être désactivé.");
            }

            self::guard($client->removeScript(VacationScript::SCRIPT_NAME), "Le script n'a pas pu être supprimé.");
        } finally {
            $this->close();
        }
    }

    public function close(): void
    {
        $this->client?->disconnect();
        $this->client = null;
    }

    /**
     * The mechanism to authenticate with, or null to let the client pick the strongest
     * one the server offers.
     *
     * Worth pinning: admin proxy auth needs PLAIN, and the client would otherwise prefer
     * DIGEST-MD5 or CRAM-MD5 when the server advertises them.
     */
    private function authMechanism(): ?string
    {
        $mechanism = config('email-management.sieve.auth_mechanism');

        return is_string($mechanism) && $mechanism !== '' ? $mechanism : null;
    }

    /**
     * Adds what the server said, and what it offered, to an otherwise bare failure.
     */
    private function describeFailure(Net_Sieve $client, string $asUser, PEAR_Error $error): string
    {
        $mechanisms = $client->getAuthMechs();

        $parts = array_filter([
            "Connexion Sieve refusée pour {$asUser}.",
            'Réponse du serveur : '.mb_trim((string) $error->getMessage(), '" '),
            is_array($mechanisms) && $mechanisms !== []
                ? 'Mécanismes proposés : '.implode(', ', $mechanisms).($this->authMechanism() !== null ? ' (demandé : '.$this->authMechanism().')' : '')
                : null,
            'Compte : '.$this->user.', pour : '.$asUser.', serveur : '.$this->host.':'.$this->port,
        ]);

        return implode(' | ', $parts);
    }
}
