<?php

declare(strict_types=1);

return [
    'node_binary' => env('PDF_NODE_BINARY', '/usr/bin/node'),
    'npm_binary' => env('PDF_NPM_BINARY', '/usr/bin/npm'),
    'node_modules_path' => env('PDF_NODE_MODULES_PATH'),
    'chrome_path' => env('PDF_CHROME_PATH', '/usr/bin/chromium'),
];
