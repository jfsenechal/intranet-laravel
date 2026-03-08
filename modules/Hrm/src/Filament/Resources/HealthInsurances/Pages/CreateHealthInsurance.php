<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages;

use AcMarche\Hrm\Filament\Resources\HealthInsurances\HealthInsuranceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateHealthInsurance extends CreateRecord
{
    protected static string $resource = HealthInsuranceResource::class;
}
