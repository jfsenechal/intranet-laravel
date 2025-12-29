<?php

namespace AcMarche\Hrm\Filament\Resources\JobFunctions\Pages;

use AcMarche\Hrm\Filament\Resources\JobFunctions\JobFunctionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListJobFunctions extends ListRecords
{
    protected static string $resource = JobFunctionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une fonction')
                ->icon('tabler-plus'),
        ];
    }
}
