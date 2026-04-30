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
     * Handle the Trip "updated" event.
     */
    public function updated(): void
    {
        // ...
    }
}
