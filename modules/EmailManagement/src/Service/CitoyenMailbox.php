<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Service;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use SplFileInfo;

/**
 * Reads a citizen's mailbox directly from the mail store, so it only works on a host that
 * has access to it.
 */
final readonly class CitoyenMailbox
{
    public function __construct(private string $homeDirectory) {}

    /**
     * Unread messages in Maildir/new, or null when the directory does not exist.
     */
    public function countNewMails(): ?int
    {
        return File::isDirectory($this->newMailPath()) ? count(File::files($this->newMailPath())) : null;
    }

    /**
     * Delivery date of the oldest message still in Maildir/new.
     *
     * Dovecot moves messages from new/ to cur/ as soon as an IMAP client opens the INBOX, so
     * this tells how long the citizen has not collected their mail.
     */
    public function oldestNewMailAt(): ?CarbonImmutable
    {
        if (! File::isDirectory($this->newMailPath())) {
            return null;
        }

        $timestamps = array_map($this->deliveryTimestamp(...), File::files($this->newMailPath()));

        return $timestamps === [] ? null : CarbonImmutable::createFromTimestamp(min($timestamps));
    }

    /**
     * @return array<int, string>
     */
    public function sieveFiles(): array
    {
        return File::glob($this->homeDirectory.'/sieve/*.sieve');
    }

    /**
     * Addresses the Sieve scripts forward the mail to.
     *
     * @return array<int, string>
     */
    public function sieveRedirects(): array
    {
        $addresses = [];

        foreach ($this->sieveFiles() as $file) {
            $script = $this->stripSieveComments(File::get($file));

            if (preg_match_all('/\bredirect\b[^;"]*"([^"]+)"/i', $script, $matches) > 0) {
                $addresses = array_merge($addresses, $matches[1]);
            }
        }

        return array_values(array_unique($addresses));
    }

    private function newMailPath(): string
    {
        return $this->homeDirectory.'/Maildir/new';
    }

    private function stripSieveComments(string $script): string
    {
        $script = preg_replace('#/\*.*?\*/#s', '', $script) ?? $script;

        return preg_replace('/^\s*#.*$/m', '', $script) ?? $script;
    }

    /**
     * Maildir file names start with the delivery timestamp (1712345678.M123P456.host).
     */
    private function deliveryTimestamp(SplFileInfo $file): int
    {
        if (preg_match('/^(\d{9,12})\./', $file->getFilename(), $matches) === 1) {
            return (int) $matches[1];
        }

        return $file->getMTime();
    }
}
