<?php

namespace AcMarche\Hrm\Filament\Resources\ContractTypes\Pages;

use AcMarche\Hrm\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListContractTypes extends ListRecords
{
    protected static string $resource = ContractTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un type')
                ->icon('tabler-plus'),
        ];
    }
}
