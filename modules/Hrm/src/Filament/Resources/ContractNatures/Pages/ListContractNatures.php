<?php

namespace AcMarche\Hrm\Filament\Resources\ContractNatures\Pages;

use AcMarche\Hrm\Filament\Resources\ContractNatures\ContractNatureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListContractNatures extends ListRecords
{
    protected static string $resource = ContractNatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une nature')
                ->icon('tabler-plus'),
        ];
    }
}
