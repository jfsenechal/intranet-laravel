<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Recipient\Pages;

use AcMarche\AldermenAgenda\Filament\Resources\Recipient\RecipientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListRecipients extends ListRecords
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' destinataires';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un destinataire')
                ->icon('tabler-plus'),
        ];
    }
}
