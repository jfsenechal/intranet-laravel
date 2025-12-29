<?php

namespace AcMarche\Hrm\Filament\Resources\Prerequisites\Pages;

use AcMarche\Hrm\Filament\Resources\Prerequisites\PrerequisiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListPrerequisites extends ListRecords
{
    protected static string $resource = PrerequisiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un prérequis')
                ->icon('tabler-plus'),
        ];
    }
}
