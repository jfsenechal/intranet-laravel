<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Service\TripAttributeResolver;

final readonly class TripObserver
{
    public function __construct(private TripAttributeResolver $tripAttributeResolver) {}

    /**
     * Handle the Trip "created" event.
     */
    public function creating(Trip $trip): void
    {
        $this->tripAttributeResolver->setRate($trip);
        $this->tripAttributeResolver->setTypeOfMovement($trip);
    }

    /**
     * Handle the Trip "updating" event.
     *
     * Recompute the rate when the departure date moves into a different
     * period, and keep the type of movement in sync with the arrival date.
     */
    public function updating(Trip $trip): void
    {
        if ($trip->isDirty('departure_date')) {
            $this->tripAttributeResolver->setRate($trip);
        }

        if ($trip->isDirty(['departure_location', 'arrival_location', 'arrival_date'])) {
            $this->tripAttributeResolver->setTypeOfMovement($trip);
        }
    }
}
