<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Repository;

use AcMarche\EmailManagement\Models\Employe;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class CitoyenRepository
{
    public static function getExpiredQuery(): Builder
    {
        $twoYearsAgo = Carbon::now()->subYears(2);

        return Employe::query()
            ->where(function (Builder $query) use ($twoYearsAgo): void {
                $query->whereNull('last_connection')
                    ->orWhere('last_connection', '<', $twoYearsAgo);
            });
    }
}
