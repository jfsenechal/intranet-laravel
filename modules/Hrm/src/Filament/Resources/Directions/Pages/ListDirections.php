<?php

namespace AcMarche\Hrm\Filament\Resources\Directions\Pages;

use AcMarche\Hrm\Filament\Resources\Directions\DirectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListDirections extends ListRecords
{
    protected static string $resource = DirectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une direction')
                ->icon('tabler-plus'),
        ];
    }
}
