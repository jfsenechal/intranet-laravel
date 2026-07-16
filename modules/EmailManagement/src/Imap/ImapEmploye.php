<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Imap;

use DirectoryTree\ImapEngine\Exceptions\ImapCapabilityException;
use DirectoryTree\ImapEngine\Exceptions\ImapConnectionFailedException;
use DirectoryTree\ImapEngine\Mailbox;
use Exception;

/**
 * Cyrus mailboxes for staff accounts, reached with the admin account over proxy auth.
 *
 * The credentials are optional: where they are absent every call throws, and callers are
 * expected to check isConfigured() first and degrade rather than fail.
 */
final class ImapEmploye
{
    /**
     * Created alongside the account itself, matching what the legacy GestEmail did.
     */
    private const array DEFAULT_FOLDERS = ['Sent', 'Trash', 'Junk', 'Drafts', 'Templates', 'Archives'];

    private ?Mailbox $mailbox = null;

    public function __construct(
        private readonly ?string $host = null,
        private readonly ?string $user = null,
        private readonly ?string $password = null,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            config('email-management.imap.employe.host'),
            config('email-management.imap.employe.user'),
            config('email-management.imap.employe.password'),
        );
    }

    public function isConfigured(): bool
    {
        return $this->host !== null && $this->host !== ''
            && $this->user !== null && $this->user !== ''
            && $this->password !== null && $this->password !== '';
    }

    /**
     * Connects as a user through the admin account (user*admin).
     *
     * @throws Exception
     */
    public function connect(string $user): Mailbox
    {
        if (! $this->isConfigured()) {
            throw new Exception('Les identifiants IMAP ne sont pas configurés (IMAP_EMPLOYE_*).');
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

        return $this->mailbox;
    }

    /**
     * Get quota for a user mailbox.
     * Returns array:
     * [
     *   "usage" => 88909
     *   "limit" => 1048576
     *   "pourcentage" => "8.48"
     * ]
     *
     * @throws Exception
     */
    public function getQuota(string $user): array
    {
        $quota = [];

        if (! $this->mailbox?->connected()) {
            $this->connect($user);
        }

        try {
            // Get the inbox folder to retrieve quota
            $folder = $this->mailbox->inbox();
            $quotaData = $folder->quota();

            // The quota() method returns an array like: ['INBOX' => ['STORAGE' => ['usage' => X, 'limit' => Y]]]
            foreach ($quotaData as $quotaValues) {
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
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        } finally {
            $this->close();
        }

        return $quota;
    }

    public function close(): void
    {
        $this->mailbox?->disconnect();
        $this->mailbox = null;
    }
}
