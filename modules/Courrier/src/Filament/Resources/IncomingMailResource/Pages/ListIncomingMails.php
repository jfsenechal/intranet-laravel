<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListIncomingMails extends ListRecords
{
    protected static string $resource = IncomingMailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter un courrier')
                ->icon('tabler-plus'),
        ];
    }
}
