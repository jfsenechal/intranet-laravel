<?php

declare(strict_types=1);

/*
 * The module keeps no local data: every reading is read live from the ISSEP API. The file
 * itself is still required because ModuleServiceProviderTrait::registerDatabaseConnection()
 * reads it for every module.
 */
return [
    'connections' => [],
];
