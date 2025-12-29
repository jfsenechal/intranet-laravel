<?php

namespace AcMarche\Hrm\Filament\Resources\JobFunctions\Pages;

use AcMarche\Hrm\Filament\Resources\JobFunctions\JobFunctionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditJobFunction extends EditRecord
{
    protected static string $resource = JobFunctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
