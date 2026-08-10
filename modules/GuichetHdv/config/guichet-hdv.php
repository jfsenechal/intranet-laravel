<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | GuichetHdv Module Configuration
    |--------------------------------------------------------------------------
    */

    /*
     * Public display page ("page écran") showing the tickets being called.
     */
    'screen_url' => env('GUICHET_HDV_SCREEN_URL', 'https://marche.local/api/guichet'),
];
