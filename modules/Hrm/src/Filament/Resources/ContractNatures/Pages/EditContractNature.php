<?php

namespace AcMarche\Hrm\Filament\Resources\ContractNatures\Pages;

use AcMarche\Hrm\Filament\Resources\ContractNatures\ContractNatureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditContractNature extends EditRecord
{
    protected static string $resource = ContractNatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
