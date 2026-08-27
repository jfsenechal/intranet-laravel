<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Sender;
use Illuminate\Support\Facades\Cache;

final class SenderRepository
{
    /**
     * How far back a sender has to have written to still be suggested.
     */
    private const int ACTIVE_YEARS = 2;

    private const int CACHE_TTL = 3600;

    /**
     * Sender names offered as autocomplete suggestions on the mail form.
     *
     * Filament renders every one of these into the field's `<datalist>`, so the
     * whole list ships inside the "Traiter" modal on each mount: the full
     * `courrier_senders` table cost a Ville admin 8 105 options, 341 KB of HTML
     * that the browser then has to parse.
     *
     * Suggesting only the senders that have actually written in the last two
     * years cuts that to 74 KB (19 KB for CPAS) and loses nothing: the names it
     * drops appear in no recent mail, so they cover 0% of what is being encoded
     * — measured coverage of recent mail is identical either way (58% CPAS,
     * 78% Ville; the remainder are senders nobody ever saved). The field stays
     * free text, so a dropped name can still be typed in full.
     *
     * @return array<int, string>
     */
    public static function forDatalist(): array
    {
        $names = [];

        foreach (DepartmentScope::getViewableDepartments() as $department) {
            foreach (self::activeInDepartment($department) as $name) {
                $names[] = $name;
            }
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    /**
     * Drop the cached suggestions of a department, so a sender just saved from
     * the mail form is suggested on the next one rather than an hour later.
     */
    public static function forgetDatalist(?string $department): void
    {
        if ($department === null) {
            return;
        }

        Cache::forget(self::cacheKey($department));
    }

    /**
     * The department's saved senders that appear on mail recent enough to
     * matter.
     *
     * Both halves are read separately and intersected in PHP: the mail side is
     * a range scan on the `mail_date` index (20-230ms) and the sender side is a
     * 1-8k row pluck, where a single `whereIn` subquery would have MariaDB
     * materialise thousands of sender strings per call.
     *
     * @return array<int, string>
     */
    private static function activeInDepartment(DepartmentCourrierEnum $department): array
    {
        return Cache::remember(
            self::cacheKey($department->value),
            self::CACHE_TTL,
            function () use ($department): array {
                $active = IncomingMail::query()
                    ->withoutGlobalScopes()
                    ->where('department', $department->value)
                    ->where('mail_date', '>=', now()->subYears(self::ACTIVE_YEARS))
                    ->whereNotNull('sender')
                    ->distinct()
                    ->pluck('sender')
                    ->flip();

                return Sender::query()
                    ->withoutGlobalScopes()
                    ->where('department', $department->value)
                    ->orderBy('name')
                    ->pluck('name')
                    ->filter(fn (string $name): bool => $active->has($name))
                    ->values()
                    ->all();
            },
        );
    }

    private static function cacheKey(string $department): string
    {
        return 'courrier.senders.datalist.'.$department;
    }
}
