<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Imap;

use DirectoryTree\ImapEngine\Exceptions\ImapCapabilityException;
use DirectoryTree\ImapEngine\Exceptions\ImapConnectionFailedException;
use DirectoryTree\ImapEngine\Mailbox;
use Exception;

/**
 * Dovecot mailboxes of the citizens, reached with a master user (user*admin).
 *
 * The credentials are optional: where they are absent every call throws, and callers are
 * expected to check isConfigured() first and degrade rather than fail.
 */
final class ImapCitoyen
{
    private ?Mailbox $mailbox = null;

    public function __construct(
        private readonly ?string $host = null,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            config('email-management.imap.citoyen.host'),
            config('email-management.imap.citoyen.user'),
            config('email-management.imap.citoyen.password'),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->host) && filled($this->user) && filled($this->password);
    }

    /**
     * Get quota for a user mailbox, in kilobytes.
     * Returns array:
     * [
     *   "usage" => 88909
     *   "limit" => 1048576
     *   "pourcentage" => "8.48"
     * ]
     *
     * @return array{usage: int, limit: int, pourcentage: string}|array{}
     *
     * @throws Exception
     */
    public function getQuota(string $user): array
    {
        $quota = [];

        $this->connect($user);

        try {
            // The quota() method returns an array like: ['INBOX' => ['STORAGE' => ['usage' => X, 'limit' => Y]]]
            foreach ($this->mailbox->inbox()->quota() as $quotaValues) {
                if (isset($quotaValues['STORAGE'])) {
                    $quota['usage'] = $quotaValues['STORAGE']['usage'];
                    $quota['limit'] = $quotaValues['STORAGE']['limit'];
                    $pourcentage = $quota['limit'] === 0 ? 100 : ($quota['usage'] * 100) / $quota['limit'];
                    $quota['pourcentage'] = number_format($pourcentage, 2);
                    break;
                }
            }
        } catch (ImapCapabilityException $e) {
            throw new Exception('Le serveur IMAP ne supporte pas les quotas: '.$e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->close();
        }

        return $quota;
    }

    /**
     * @throws Exception
     */
    private function connect(string $user): void
    {
        if (! $this->isConfigured()) {
            throw new Exception('Les identifiants IMAP ne sont pas configurés (IMAP_CITOYEN_URL, EMAIL_MANAGEMENT_CITOYEN_DOVECOT_ADMIN_*).');
        }

        $this->mailbox = new Mailbox([
            'host' => $this->host,
            'port' => 993,
            'encryption' => 'ssl',
            'username' => $user.'*'.$this->user,
            'password' => $this->password,
            'validate_cert' => false,
        ]);

        try {
            $this->mailbox->connect();
        } catch (ImapConnectionFailedException $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function close(): void
    {
        $this->mailbox?->disconnect();
        $this->mailbox = null;
    }
}
