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

    public function setTypeOfMovement(Trip $trip): void
    {
        if ($trip->arrival_date !== null) {
            $trip->type_movement = TypeMovementEnum::EXTERNAL->value;

            return;
        }
        $trip->type_movement = TypeMovementEnum::INTERNAL->value;
    }
}
