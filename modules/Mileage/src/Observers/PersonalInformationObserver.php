<?php

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\PersonalInformation;

final class PersonalInformationObserver
{
    /**
     * Handle the PersonalInformation "creating" event.
     */
    public function creating(PersonalInformation $personalInformation): void
    {
        $personalInformation->username = auth()->user()?->username;
    }

    /**
     * doesn't work
     * @param PersonalInformation $personalInformation
     * @return void
     */
    public function created(PersonalInformation $personalInformation): void
    {
        $personalInformation->username = auth()->user()?->username;
    }
}
