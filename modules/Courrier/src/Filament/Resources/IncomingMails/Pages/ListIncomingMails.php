<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListIncomingMails extends ListRecords
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un courrier')
                ->icon('tabler-plus'),
        ];
    }
}
