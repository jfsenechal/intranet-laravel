<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;

final class TripHandler
{
    public function setRate(Trip $trip): void
    {
        $rate = Rate::query()
            ->where('start_date', '<=', $trip->departure_date)
            ->where(function ($query) use ($trip) {
                $query->where('end_date', '>=', $trip->departure_date)
                    ->orWhereNull('end_date');
            })
            ->orderBy('start_date', 'desc')
            ->first();

        if ($rate) {
            $trip->rate = $rate->amount;
            $trip->omnium = $rate->omnium;
        }
    }

    public function setTypeOfMovement(Trip $trip): void
    {
        if ($trip->arrival_date != null) {
            $trip->type_movement = TypeMovementEnum::EXTERNAL->value;

            return;
        }
        $trip->type_movement = TypeMovementEnum::INTERNAL->value;
    }
}
