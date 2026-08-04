<?php

declare(strict_types=1);

/*
 * Here you can define your own helper functions.
 * Make sure to use the `function_exists` check to not declare the function twice.
 */

if (! function_exists('example')) {
    function example(): string
    {
        return 'This is an example function you can use in your project.';
    }
}

if (! function_exists('display_datetime')) {
    /**
     * Format a timestamp in the application's display timezone.
     *
     * Timestamps are stored and manipulated in UTC (`app.timezone`), so anything
     * shown to a user must be converted first. Filament components already do
     * this through `FilamentTimezone`; use this helper everywhere else (mail
     * views, PDF views, exports).
     */
    function display_datetime(?DateTimeInterface $date, string $format = 'd/m/Y H:i'): ?string
    {
        if ($date === null) {
            return null;
        }

        return Carbon\Carbon::instance($date)
            ->timezone(config('app.display_timezone'))
            ->format($format);
    }
}
