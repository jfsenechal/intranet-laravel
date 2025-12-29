<?php

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Service\TripAttributeResolver;

class TripObserver
{
    public function __construct(private readonly TripAttributeResolver $tripAttributeResolver)
    {
    }

    /**
     * Handle the Trip "created" event.
     */
    public function creating(Trip $trip): void
    {
        $this->tripAttributeResolver->setRate($trip);
        $this->tripAttributeResolver->setTypeOfMovement($trip);
    }

    /**
     * Handle the Trip "created" event.
     */
    public function created(Trip $trip): void
    {

    }

    /**
     * Handle the Trip "updated" event.
     */
    public function updated(Trip $trip): void
    {
        // ...
    }
}
