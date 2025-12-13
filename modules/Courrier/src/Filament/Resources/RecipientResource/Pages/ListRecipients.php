<?php

namespace AcMarche\Courrier\Filament\Resources\RecipientResource\Pages;

use AcMarche\Courrier\Filament\Resources\RecipientResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

final class ListRecipients extends ListRecords
{
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' destinataires';
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
