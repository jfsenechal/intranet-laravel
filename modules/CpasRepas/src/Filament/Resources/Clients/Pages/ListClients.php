<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Clients\Pages;

use AcMarche\CpasRepas\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListClients extends ListRecords
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return 'Clients';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add client')
                ->icon('tabler-plus'),
        ];
    }
}
