<?php

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Handler\TripHandler;
use AcMarche\Mileage\Models\Trip;

class TripObserver
{
    public function __construct(private readonly TripHandler $tripHandler)
    {
    }

    /**
     * Handle the Trip "created" event.
     */
    public function creating(Trip $trip): void
    {
        $this->tripHandler->setRate($trip);
        $this->tripHandler->setTypeOfMovement($trip);
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
