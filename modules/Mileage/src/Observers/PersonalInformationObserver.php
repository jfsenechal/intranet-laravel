<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Observers;

use AcMarche\Mileage\Models\PersonalInformation;

final class PersonalInformationObserver
{
    public function creating(PersonalInformation $personalInformation): void
    {
        $personalInformation->username ??= auth()->user()->username;
    }
}
