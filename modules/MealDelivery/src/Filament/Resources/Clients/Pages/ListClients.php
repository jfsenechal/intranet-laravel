<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Pages;

use AcMarche\MealDelivery\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListClients extends ListRecords
{
    #[Override]
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' clients';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un client')
                ->icon('tabler-plus'),
        ];
    }
}
