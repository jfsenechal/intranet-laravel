<?php

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\PersonalInformation;

final class PersonalInformationObserver
{
    public function __construct() {}

    /**
     * Handle the PersonalInformation "created" event.
     */
    public function created(PersonalInformation $personalInformation): void
    {
        $personalInformation->username = auth()->user()?->name;
    }
}
