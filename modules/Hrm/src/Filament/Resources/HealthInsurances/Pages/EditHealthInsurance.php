<?php

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages;

use AcMarche\Hrm\Filament\Resources\HealthInsurances\HealthInsuranceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditHealthInsurance extends EditRecord
{
    protected static string $resource = HealthInsuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
