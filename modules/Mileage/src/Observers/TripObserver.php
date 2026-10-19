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
    }

    /**
     * Handle the Trip "updating" event.
     *
     * Recompute the rate when the departure date moves into a different
     * period.
     */
    public function updating(Trip $trip): void
    {
        if ($trip->isDirty('departure_date')) {
            $this->tripAttributeResolver->setRate($trip);
        }
    }
}
