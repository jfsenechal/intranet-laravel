<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Calculator;

use AcMarche\Mileage\Models\Trip;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for a trip's reimbursable amount.
 *
 * The amount of a trip is the mileage allowance net of the omnium share:
 * `distance * (rate - omnium)`.
 */
final class TripAmountCalculator
{
    /**
     * SQL expression matching {@see self::forTrip()}, used for database-side
     * aggregations such as table summaries.
     */
    public const string SUM_EXPRESSION = 'distance * (COALESCE(rate, 0) - COALESCE(omnium, 0))';

    /**
     * Net amount for a number of kilometers: `kilometers * (rate - omnium)`.
     *
     * Pass `omnium` as `0.0` (the default) to obtain the gross mileage allowance.
     */
    public function amount(int $kilometers, float $rate, float $omnium = 0.0): float
    {
        return round($kilometers * ($rate - $omnium), 2);
    }

    public function forTrip(Trip $trip): float
    {
        return $this->amount((int) $trip->distance, (float) $trip->rate, (float) $trip->omnium);
    }

    /**
     * Total amount for all trips matching the given query, computed in the database.
     */
    public function forQuery(Builder $query): float
    {
        return round((float) $query->sum(DB::raw(self::SUM_EXPRESSION)), 2);
    }
}
