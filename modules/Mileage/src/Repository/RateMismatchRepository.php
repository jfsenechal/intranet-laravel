<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Repository;

use AcMarche\Mileage\Models\Trip;
use Illuminate\Database\Query\JoinClause;

final class RateMismatchRepository
{
    /**
     * Declarations reimbursed at a rate that is not the official one for the
     * dates of their trips.
     *
     * A declared trip is reimbursed at declaration->rate x distance
     * (DeclarationCalculator), so the rate stored on the trip row never enters
     * the reimbursement. The comparison is therefore made on the declaration's
     * rate: a trip row that disagrees with its own declaration is a storage
     * inconsistency without a euro at stake, and is the business of
     * mileage:verify-trip-rates, not of this report.
     *
     * @return array<int, array{
     *     id: int,
     *     last_name: string,
     *     first_name: string,
     *     user_add: string|null,
     *     declared_at: string|null,
     *     paid: float,
     *     trip_count: int,
     *     delta: float,
     *     trips: array<int, array{date: string, distance: int, paid: float, official: float, delta: float}>
     * }>
     */
    public static function findSince(int $year): array
    {
        $rows = Trip::query()
            ->join('declarations', 'declarations.id', '=', 'trips.declaration_id')
            ->join('rates', fn (JoinClause $join) => $join->whereRaw(
                'DATE(trips.departure_date) BETWEEN rates.start_date AND rates.end_date'
            ))
            ->whereNull('declarations.deleted_at')
            ->where('trips.declaration_id', '>', 0)
            ->whereRaw('CAST(declarations.rate AS DECIMAL(10,4)) <> CAST(rates.amount AS DECIMAL(10,4))')
            ->whereYear('trips.departure_date', '>=', $year)
            ->orderBy('declarations.id')
            ->orderBy('trips.departure_date')
            ->orderBy('trips.id')
            ->get([
                'declarations.id as declaration_id',
                'declarations.last_name',
                'declarations.first_name',
                'declarations.user_add',
                'declarations.created_at as declared_at',
                'declarations.rate as paid',
                'trips.departure_date',
                'trips.distance',
                'rates.amount as official',
            ]);

        $declarations = [];

        foreach ($rows as $row) {
            $id = (int) $row->declaration_id;
            $paid = (float) $row->paid;
            $official = (float) $row->official;
            $distance = (int) $row->distance;
            // Rounded per trip, then summed, so the detail adds up to the total.
            $delta = round(($official - $paid) * $distance, 2);

            $declarations[$id] ??= [
                'id' => $id,
                'last_name' => (string) $row->last_name,
                'first_name' => (string) $row->first_name,
                'user_add' => $row->user_add,
                'declared_at' => $row->declared_at,
                'paid' => $paid,
                'trip_count' => 0,
                'delta' => 0.0,
                'trips' => [],
            ];

            $declarations[$id]['trip_count']++;
            $declarations[$id]['delta'] = round($declarations[$id]['delta'] + $delta, 2);
            $declarations[$id]['trips'][] = [
                'date' => (string) $row->departure_date,
                'distance' => $distance,
                'paid' => $paid,
                'official' => $official,
                'delta' => $delta,
            ];
        }

        return array_values($declarations);
    }

    /**
     * Trips whose stored rate disagrees with the declaration they were filed
     * under. No reimbursement is affected; mileage:verify-trip-rates --fix
     * realigns them.
     */
    public static function countStorageInconsistenciesSince(int $year): int
    {
        return Trip::query()
            ->join('declarations', 'declarations.id', '=', 'trips.declaration_id')
            ->whereNull('declarations.deleted_at')
            ->where('trips.declaration_id', '>', 0)
            ->whereRaw('CAST(trips.rate AS DECIMAL(10,4)) <> CAST(declarations.rate AS DECIMAL(10,4))')
            ->whereYear('trips.departure_date', '>=', $year)
            ->count();
    }
}
