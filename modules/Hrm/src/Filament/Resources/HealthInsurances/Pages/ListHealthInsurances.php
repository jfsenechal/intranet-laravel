<?php

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages;

use AcMarche\Hrm\Filament\Resources\HealthInsurances\HealthInsuranceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListHealthInsurances extends ListRecords
{
    protected static string $resource = HealthInsuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une mutuelle')
                ->icon('tabler-plus'),
        ];
    }
}
