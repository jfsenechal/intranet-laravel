<?php

namespace AcMarche\Hrm\Filament\Resources\Services\Pages;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un service')
                ->icon('tabler-plus'),
        ];
    }
}
