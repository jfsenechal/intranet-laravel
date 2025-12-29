<?php

namespace AcMarche\Hrm\Filament\Resources\ContractTypes\Pages;

use AcMarche\Hrm\Filament\Resources\ContractTypes\ContractTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditContractType extends EditRecord
{
    protected static string $resource = ContractTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
