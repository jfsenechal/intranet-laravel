<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Service\TripAttributeResolver;

final readonly class RateObserver
{
    public function __construct(private TripAttributeResolver $tripAttributeResolver) {}

    /**
     * Handle the Rate "created" event.
     *
     * Trips encoded before the rate existed kept the rate applicable at the
     * time, so back-fill the ones still open for declaration.
     */
    public function created(Rate $rate): void
    {
        $this->tripAttributeResolver->applyRateToUndeclaredTrips($rate);
    }

    /**
     * Handle the Rate "updated" event.
     *
     * Correcting an amount, or moving the period, leaves the trips still open
     * for declaration carrying what the rate said before the edit.
     *
     * Trips the period no longer covers are deliberately left as they are:
     * periods cannot overlap, so no other rate applies to them and there is
     * nothing to put in place of their current amounts. They become
     * declarable again once a rate covers their departure date, at which
     * point DeclarationFactory resolves the rate afresh anyway.
     */
    public function updated(Rate $rate): void
    {
        if (! $rate->wasChanged(['amount', 'omnium', 'start_date', 'end_date'])) {
            return;
        }

        $this->tripAttributeResolver->applyRateToUndeclaredTrips($rate);
    }
}
