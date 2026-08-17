<?php

declare(strict_types=1);

namespace AcMarche\Issep\Exceptions;

use RuntimeException;

/**
 * Any failure to obtain usable data from the ISSEP API: unreachable host, HTTP error
 * (an expired token answers 401), or a body that is not the JSON array expected.
 */
final class IssepException extends RuntimeException {}
