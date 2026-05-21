<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Boots the application without migrating the database.
 *
 * The Conseil service tests only exercise the HTTP and storage layers,
 * so they avoid the full multi-connection migration setup.
 */
abstract class ConseilTestCase extends BaseTestCase {}
