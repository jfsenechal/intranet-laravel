<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Service;

use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;

final class TripAttributeResolver
{
    public function setRate(Trip $trip): void
    {
        $rate = $this->resolveRate($trip);

        if ($rate instanceof Rate) {
            $trip->rate = $rate->amount;
            $trip->omnium = $rate->omnium;
        }
    }

    /**
     * Resolve the Rate applicable to the trip's departure date, if any.
     */
    public function resolveRate(Trip $trip): ?Rate
    {
        return Rate::query()
            ->where('start_date', '<=', $trip->departure_date)
            ->where(function ($query) use ($trip): void {
                $query->where('end_date', '>=', $trip->departure_date)
                    ->orWhereNull('end_date');
            })
            ->latest('start_date')
            ->first();
    }

    /**
     * Apply a rate to every trip within its period that is not yet linked to a
     * declaration. Declared trips are left untouched: their rate is a snapshot
     * of what was declared, and correcting those is the job of the
     * mileage:verify-trip-rates command.
     *
     * @return int The number of trips updated.
     */
    public function applyRateToUndeclaredTrips(Rate $rate): int
    {
        return Trip::query()
            ->where(function ($query): void {
                $query->whereNull('declaration_id')
                    ->orWhere('declaration_id', '<=', 0);
            })
            ->where('departure_date', '>=', $rate->start_date)
            ->when(
                $rate->end_date !== null,
                fn ($query) => $query->where('departure_date', '<=', $rate->end_date),
            )
            ->update([
                'rate' => $rate->amount,
                'omnium' => $rate->omnium,
            ]);
    }

    public function setTypeOfMovement(Trip $trip): void
    {
        $isExternal = filled($trip->departure_location)
            && filled($trip->arrival_location)
            && $trip->arrival_date !== null;

        $trip->type_movement = $isExternal
            ? TypeMovementEnum::EXTERNAL->value
            : TypeMovementEnum::INTERNAL->value;
    }
}
