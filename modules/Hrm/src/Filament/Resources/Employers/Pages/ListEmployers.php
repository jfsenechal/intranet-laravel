<?php

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListEmployers extends ListRecords
{
    protected static string $resource = EmployerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un employeur')
                ->icon('tabler-plus'),
        ];
    }
}
