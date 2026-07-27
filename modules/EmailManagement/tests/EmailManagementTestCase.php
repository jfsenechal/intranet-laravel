<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Tests;

use Tests\TestCase;

/**
 * Keeps EmailManagement tests off the live mail server.
 *
 * The maria-email-management connection itself is handled by Tests\TestCase,
 * which rewrites it to sqlite and gives it its own in-memory PDO. That has to
 * happen for every test in the process, not just this module's: phpunit.xml only
 * overrides the default connection, so a test case that left this connection
 * alone would resolve it to the live MariaDB from .env, and its migrations would
 * run against a schema this module's tests never see.
 */
abstract class EmailManagementTestCase extends TestCase
{
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        // The IMAP and Sieve credentials in .env point at the production mail server, and
        // ImapEmploye/SieveEmploye reach for it as soon as they are configured. Blanking
        // them here makes both report themselves unconfigured, so a test renders the quota
        // as unavailable instead of opening a socket to mail.marche.be. Tests that need
        // these services bind their own doubles.
        $this->app['config']->set('email-management.imap', null);
        $this->app['config']->set('email-management.sieve', null);
    }
}
