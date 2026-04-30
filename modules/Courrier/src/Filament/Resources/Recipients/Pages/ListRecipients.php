<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Recipients\Pages;

use AcMarche\Courrier\Filament\Resources\Recipients\RecipientResource;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListRecipients extends ListRecords
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getSubheading(): string
    {
        return 'La liste des destinataires est synchronisée avec le système informique';
    }

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' destinataires';
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
